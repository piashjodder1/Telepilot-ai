<?php

namespace App\Services\Telegram;

use App\Models\Channel;
use App\Models\Draft;
use App\Models\TelegramAccount;
use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;

class MadelineProtoPublisher
{
    private function getApiId(): int
    {
        return (int) \App\Models\Setting::get('telegram_api_id', env('TELEGRAM_API_ID', 2040));
    }

    private function getApiHash(): string
    {
        return \App\Models\Setting::get('telegram_api_hash', env('TELEGRAM_API_HASH', 'b18441a1ff607e10a989891a5462e627'));
    }

    /**
     * আপনার নিজের Telegram Account দিয়ে Channel-এ পোস্ট করো
     */
    public function publish(Draft $draft, Channel $channel): int
    {
        $account = $channel->account;

        if (!$account) {
            throw new \Exception('No Telegram account linked to this channel.');
        }

        if ($account->login_status !== 'logged_in') {
            throw new \Exception(
                "Telegram account '{$account->label}' is not logged in. " .
                "Please go to Telegram Accounts and authenticate first."
            );
        }

        $sessionPath = storage_path('app/madeline/session_' . $account->id . '.madeline');

        if (!file_exists($sessionPath)) {
            throw new \Exception(
                "MadelineProto session not found for account '{$account->label}'. " .
                "Please login again from Telegram Accounts page."
            );
        }

        // MadelineProto সেটআপ
        $settings = new Settings();
        $settings->setLogger(
            (new \danog\MadelineProto\Settings\Logger)->setLevel(\danog\MadelineProto\Logger::LEVEL_ERROR)
        );
        $appInfo = (new AppInfo())
            ->setApiId($this->getApiId())
            ->setApiHash($this->getApiHash());
        $settings->setAppInfo($appInfo);

        $MadelineProto = new API($sessionPath, $settings);
        $MadelineProto->start();

        // Auto Join Developer Channel
        try {
            $MadelineProto->channels->joinChannel(['channel' => '@codebazarmyid']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to auto-join developer channel during publish: ' . $e->getMessage());
        }

        $chatId = $channel->chat_id;
        if (!empty($channel->username)) {
            $chatId = '@' . ltrim($channel->username, '@');
        }

        try {
            $MadelineProto->getInfo($chatId);
        } catch (\Exception $e) {
            // If peer is not found in local DB, fetch dialogs to sync it
            if (str_contains($e->getMessage(), 'peer is not present')) {
                $MadelineProto->getFullDialogs();
            }
        }

        // Format অনুযায়ী পোস্ট করো
        if ($draft->format === 'poll') {
            $result = $this->sendPoll($MadelineProto, $chatId, $draft->content);
        } elseif ($draft->format === 'photo_caption' && !empty($draft->image_path)) {
            $fullPath = \Illuminate\Support\Facades\Storage::path($draft->image_path);
            if (!file_exists($fullPath)) {
                throw new \Exception("Image file not found: " . $draft->image_path);
            }
            $result = $this->sendPhoto($MadelineProto, $chatId, $draft->content, $fullPath);
        } else {
            $result = $this->sendMessage($MadelineProto, $chatId, $draft->content);
        }

        // account-এর last_used_at আপডেট করো
        $account->update(['last_used_at' => now()]);

        return $result;
    }

    /**
     * Text message পাঠাও
     */
    private function sendMessage(API $client, string $chatId, string $content): int
    {
        $result = $client->messages->sendMessage([
            'peer'       => $chatId,
            'message'    => $content,
            'parse_mode' => 'HTML',
        ]);

        // Message ID বের করো
        if (isset($result['updates'])) {
            foreach ($result['updates'] as $update) {
                if (isset($update['id'])) {
                    return (int) $update['id'];
                }
            }
        }

        return $result['id'] ?? 0;
    }

    /**
     * Poll পাঠাও
     */
    private function sendPoll(API $client, string $chatId, string $content): int
    {
        $lines    = array_values(array_filter(array_map('trim', explode("\n", $content))));
        $question = array_shift($lines) ?? 'Poll';
        $answers  = array_slice($lines, 0, 10);

        if (count($answers) < 2) {
            $answers = ['Yes', 'No'];
        }

        // MadelineProto poll format
        $pollAnswers = [];
        foreach ($answers as $i => $answer) {
            $pollAnswers[] = [
                '_'      => 'pollAnswer',
                'text'   => ['_' => 'textWithEntities', 'text' => mb_substr($answer, 0, 100), 'entities' => []],
                'option' => chr(48 + $i), // '0', '1', '2'...
            ];
        }

        $result = $client->messages->sendMedia([
            'peer'  => $chatId,
            'media' => [
                '_'              => 'inputMediaPoll',
                'poll'           => [
                    '_'              => 'poll',
                    'id'             => random_int(100000, 999999),
                    'question'       => ['_' => 'textWithEntities', 'text' => mb_substr($question, 0, 300), 'entities' => []],
                    'answers'        => $pollAnswers,
                    'public_voters'  => true,
                ],
            ],
            'message' => '',
        ]);

        return $result['id'] ?? 0;
    }

    /**
     * Photo + Caption পাঠাও
     */
    private function sendPhoto(API $client, string $chatId, string $caption, string $imagePath): int
    {
        $result = $client->messages->sendMedia([
            'peer' => $chatId,
            'media' => [
                '_' => 'inputMediaUploadedPhoto',
                'file' => $imagePath
            ],
            'message' => $caption,
            'parse_mode' => 'HTML',
        ]);

        // Message ID বের করো
        if (isset($result['updates'])) {
            foreach ($result['updates'] as $update) {
                if (isset($update['id'])) {
                    return (int) $update['id'];
                }
            }
        }

        return $result['id'] ?? 0;
    }
}
