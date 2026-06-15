<?php

namespace App\Http\Controllers;

use App\Models\Draft;
use App\Models\ScheduledPost;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $botToken = \App\Models\Setting::get('system_bot_token');
        if (!$botToken) {
            return response()->json(['status' => 'error', 'message' => 'No bot token configured']);
        }

        $update = $request->all();

        // Handle Callback Queries (Button Clicks)
        if (isset($update['callback_query'])) {
            $callback = $update['callback_query'];
            $data = $callback['data'];
            $chatId = $callback['message']['chat']['id'];
            $messageId = $callback['message']['message_id'];
            $callbackId = $callback['id'];

            if (str_starts_with($data, 'approve_') || str_starts_with($data, 'reject:')) {
                $this->handleCallback($data, $chatId, $messageId, $callbackId, $botToken);
            }
        }

        // Handle Text Messages (Custom Time input)
        if (isset($update['message']['text'])) {
            $text = $update['message']['text'];
            $chatId = $update['message']['chat']['id'];
            $replyToMessage = $update['message']['reply_to_message'] ?? null;

            if ($replyToMessage && str_contains($replyToMessage['text'] ?? '', 'New AI Draft Pending Approval')) {
                $this->handleCustomTimeReply($text, $chatId, $replyToMessage['text'], $botToken);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleCallback($data, $chatId, $messageId, $callbackId, $botToken)
    {
        $parts = explode(':', $data);
        $action = $parts[0];
        $draftId = $parts[1] ?? null;

        if (!$draftId) return;

        $draft = Draft::find($draftId);
        if (!$draft) {
            $this->answerCallback($callbackId, $botToken, 'Draft not found or already processed.');
            return;
        }

        if ($draft->status !== 'draft') {
            $this->answerCallback($callbackId, $botToken, "Draft is already {$draft->status}.");
            return;
        }

        $scheduledAt = null;
        $statusMessage = '';

        switch ($action) {
            case 'approve_now':
                $scheduledAt = now();
                $statusMessage = '✅ Approved for immediate posting.';
                break;
            case 'approve_30m':
                $scheduledAt = now()->addMinutes(30);
                $statusMessage = '✅ Approved. Scheduled in 30 minutes.';
                break;
            case 'approve_1h':
                $scheduledAt = now()->addHour();
                $statusMessage = '✅ Approved. Scheduled in 1 hour.';
                break;
            case 'reject':
                $draft->update(['status' => 'failed', 'fail_reason' => 'Rejected by Admin']);
                $this->answerCallback($callbackId, $botToken, '❌ Draft rejected.');
                $this->updateMessageText($chatId, $messageId, $botToken, "❌ <b>Draft Rejected!</b>\n\nRule: {$draft->rule->name}");
                return;
        }

        if ($scheduledAt) {
            ScheduledPost::create([
                'draft_id'     => $draft->id,
                'channel_id'   => $draft->channel_id,
                'rule_id'      => $draft->rule_id,
                'scheduled_at' => $scheduledAt,
                'status'       => 'scheduled',
                'attempts'     => 0,
            ]);

            $draft->update(['status' => 'scheduled']);
            $this->answerCallback($callbackId, $botToken, $statusMessage);
            $this->updateMessageText($chatId, $messageId, $botToken, "✅ <b>Draft Scheduled!</b>\n\nScheduled for: " . $scheduledAt->timezone('Asia/Dhaka')->format('d M Y, h:i A'));
        }
    }

    private function handleCustomTimeReply($text, $chatId, $originalMessageText, $botToken)
    {
        // Extract Rule Name or try to find latest pending draft for this chat
        $draft = Draft::where('status', 'draft')->latest()->first();

        if (!$draft) {
            $this->sendMessage($chatId, $botToken, "❌ Could not find a pending draft to approve.");
            return;
        }

        try {
            // Parse time assuming Asia/Dhaka
            $time = Carbon::parse($text, 'Asia/Dhaka');
            
            if ($time->isPast()) {
                // If it's a time like "05:30 PM" and it already passed today, assume tomorrow
                if (strlen(trim($text)) <= 8) { // basic heuristic for time-only strings
                    $time->addDay();
                } else {
                    $this->sendMessage($chatId, $botToken, "❌ The time you provided is in the past.");
                    return;
                }
            }

            ScheduledPost::create([
                'draft_id'     => $draft->id,
                'channel_id'   => $draft->channel_id,
                'rule_id'      => $draft->rule_id,
                'scheduled_at' => $time->utc(), // Convert back to UTC for database
                'status'       => 'scheduled',
                'attempts'     => 0,
            ]);

            $draft->update(['status' => 'scheduled']);
            $this->sendMessage($chatId, $botToken, "✅ <b>Custom Time Accepted!</b>\n\nDraft Scheduled for: " . $time->format('d M Y, h:i A'));

        } catch (\Exception $e) {
            $this->sendMessage($chatId, $botToken, "❌ Could not parse the time. Please use a format like '05:30 PM' or '12 Jun 08:15 AM'.");
        }
    }

    private function answerCallback($callbackId, $botToken, $text)
    {
        Http::post("https://api.telegram.org/bot{$botToken}/answerCallbackQuery", [
            'callback_query_id' => $callbackId,
            'text' => $text,
            'show_alert' => false
        ]);
    }

    private function updateMessageText($chatId, $messageId, $botToken, $text)
    {
        Http::post("https://api.telegram.org/bot{$botToken}/editMessageText", [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    private function sendMessage($chatId, $botToken, $text)
    {
        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }
}
