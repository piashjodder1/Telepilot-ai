<?php

namespace App\Jobs;

use App\Models\Draft;
use App\Models\ScheduledPost;
use App\Models\PublishedPost;
use App\Services\Telegram\TelegramPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;
    public $backoff = [120, 600]; // 2 mins, then 10 mins

    public function __construct(public ScheduledPost $scheduledPost)
    {
    }

    public function handle(TelegramPublisher $publisher): void
    {
        $this->scheduledPost->update(['status' => 'processing', 'last_attempt_at' => now()]);
        
        $draft = $this->scheduledPost->draft;
        $channel = $this->scheduledPost->channel;

        try {
            $lockKey = 'madeline_session_' . $channel->account_id;
            $messageId = \Illuminate\Support\Facades\Cache::lock($lockKey, 60)->block(30, function () use ($publisher, $draft, $channel) {
                return $publisher->publish($draft, $channel);
            });

            PublishedPost::create([
                'scheduled_post_id' => $this->scheduledPost->id,
                'channel_id' => $channel->id,
                'draft_id' => $draft->id,
                'telegram_message_id' => $messageId,
                'content_preview' => mb_substr($draft->content, 0, 100),
                'published_at' => now(),
            ]);

            $this->scheduledPost->update(['status' => 'published']);
            $draft->update(['status' => 'published']);
            
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            // Lock timeout implies another process is actively using the session. Throw to retry later.
            throw new \Exception("MadelineProto session is busy. " . $e->getMessage());
        } catch (\Exception $e) {
            if ($this->attempts() >= $this->tries) {
                $this->scheduledPost->update([
                    'status' => 'failed',
                    'fail_reason' => $e->getMessage(),
                ]);
                $draft->update([
                    'status' => 'failed',
                    'fail_reason' => $e->getMessage(),
                ]);
                
                // Notify via System Bot
                $this->notifySystemBot("❌ *Post Publish Failed*\n\n*Channel:* {$channel->title}\n*Reason:* " . $e->getMessage());
            }
            
            throw $e;
        }
    }

    protected function notifySystemBot(string $message): void
    {
        $botToken = \App\Models\Setting::get('system_bot_token');
        $adminChatId = \App\Models\Setting::get('admin_chat_id'); // If defined, otherwise maybe broadcast or skip
        
        if ($botToken && $adminChatId) {
            \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $adminChatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        }
    }
}
