<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$accountId = 1;
$sessionPath = storage_path('app/madeline/session_' . $accountId . '.madeline');

if (!file_exists($sessionPath)) {
    die("Session file not found.\n");
}

$settings = new \danog\MadelineProto\Settings();
$settings->setLogger((new \danog\MadelineProto\Settings\Logger)->setLevel(\danog\MadelineProto\Logger::LEVEL_ERROR));
$MadelineProto = new \danog\MadelineProto\API($sessionPath, $settings);

echo "Fetching dialogs...\n";
try {
    $dialogs = $MadelineProto->getFullDialogs();

    foreach ($dialogs as $dialog) {
        try {
            $info = $MadelineProto->getInfo($dialog);
            if (isset($info['type']) && in_array($info['type'], ['channel', 'supergroup'])) {
                echo "Title: " . ($info['Chat']['title'] ?? 'Unknown') . "\n";
                echo "Is Creator: " . (isset($info['Chat']['creator']) && $info['Chat']['creator'] ? 'Yes' : 'No') . "\n";
                echo "Has Admin Rights: " . (isset($info['Chat']['admin_rights']) ? 'Yes' : 'No') . "\n";
                echo "------------------------\n";
            }
        } catch (\Exception $e) {
            // Ignore
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
