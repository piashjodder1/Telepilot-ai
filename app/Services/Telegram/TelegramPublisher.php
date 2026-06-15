<?php

namespace App\Services\Telegram;

use App\Models\Channel;
use App\Models\Draft;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TelegramPublisher — স্মার্ট Publisher Dispatcher
 *
 * অটোমেটিক সিদ্ধান্ত নেয়:
 *   1. Account-এ MadelineProto session + login_status='logged_in' থাকলে → User Account দিয়ে পোস্ট
 *   2. না থাকলে → Bot Token দিয়ে পোস্ট (fallback)
 */
class TelegramPublisher
{
    public function __construct(
        protected MadelineProtoPublisher $madelinePublisher
    ) {}

    public function publish(Draft $draft, Channel $channel): int
    {
        $account = $channel->account;

        if (!$account) {
            throw new \Exception("No Telegram account linked to channel '{$channel->title}'.");
        }

        // ── পদ্ধতি নির্বাচন ──
        // MadelineProto session আছে এবং logged_in → user account দিয়ে পোস্ট
        $sessionPath    = storage_path('app/madeline/session_' . $account->id . '.madeline');
        $hasMadeline    = $account->login_status === 'logged_in' && file_exists($sessionPath);

        if ($hasMadeline) {
            Log::info("Publishing via MadelineProto (account: {$account->label}) to channel: {$channel->title}");
            return $this->madelinePublisher->publish($draft, $channel);
        }

        // Fallback → Bot API
        if (!empty($account->bot_token)) {
            Log::info("Publishing via Bot API (account: {$account->label}) to channel: {$channel->title}");
            return $this->publishViaBotApi($draft, $channel);
        }

        throw new \Exception(
            "Cannot publish: Account '{$account->label}' has no active MadelineProto session " .
            "and no Bot Token. Please login from the Telegram Accounts page."
        );
    }

    /**
     * Bot API দিয়ে পোস্ট (fallback)
     */
    private function publishViaBotApi(Draft $draft, Channel $channel): int
    {
        $account  = $channel->account;
        $botToken = $account->bot_token;
        $chatId   = $channel->chat_id;

        if ($draft->format === 'poll') {
            $url   = "https://api.telegram.org/bot{$botToken}/sendPoll";
            $lines = array_values(array_filter(array_map('trim', explode("\n", $draft->content))));
            $question = array_shift($lines) ?? 'Poll';
            $options  = array_slice($lines, 0, 10);

            if (count($options) < 2) {
                $options = ['Yes', 'No'];
            }

            $response = Http::post($url, [
                'chat_id'      => $chatId,
                'question'     => mb_substr($question, 0, 300),
                'options'      => json_encode($options),
                'is_anonymous' => false,
            ]);
        } elseif ($draft->format === 'photo_caption' && !empty($draft->image_path)) {
            $url      = "https://api.telegram.org/bot{$botToken}/sendPhoto";
            $fullPath = \Illuminate\Support\Facades\Storage::path($draft->image_path);
            
            if (file_exists($fullPath)) {
                $response = Http::attach(
                    'photo', file_get_contents($fullPath), basename($fullPath)
                )->post($url, [
                    'chat_id'    => $chatId,
                    'caption'    => $draft->content,
                    'parse_mode' => 'HTML',
                ]);
            } else {
                throw new \Exception("Image file not found: " . $draft->image_path);
            }
        } else {
            $url      = "https://api.telegram.org/bot{$botToken}/sendMessage";
            $response = Http::post($url, [
                'chat_id'    => $chatId,
                'text'       => $draft->content,
                'parse_mode' => 'HTML',
            ]);
        }

        if ($response->successful()) {
            return $response->json('result.message_id') ?? 0;
        }

        throw new \Exception("Telegram Bot API Error: " . $response->body());
    }
}
