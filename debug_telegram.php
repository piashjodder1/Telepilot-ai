<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ch = \App\Models\Channel::find(20);
echo "Channel: {$ch->title}\n";
echo "chat_id: {$ch->chat_id}\n";
echo "type: {$ch->type}\n";

$account = $ch->account;
echo "\nTelegram Account: {$account->label}\n";
$token = $account->bot_token;
echo "Bot Token (masked): " . substr($token, 0, 10) . "..." . substr($token, -5) . "\n";

// Test bot token
$resp = \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/getMe");
echo "\nBot getMe: " . ($resp->successful() ? "✅ Valid - " . $resp->json('result.username') : "❌ Invalid - " . $resp->body()) . "\n";

// Test chat
$resp2 = \Illuminate\Support\Facades\Http::get("https://api.telegram.org/bot{$token}/getChat", ['chat_id' => $ch->chat_id]);
echo "getChat: " . ($resp2->successful() ? "✅ Found - " . $resp2->json('result.title') : "❌ Error - " . $resp2->body()) . "\n";
