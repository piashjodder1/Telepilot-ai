<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$account = App\Models\TelegramAccount::find(2);
$sessionPath = storage_path('app/madeline/session_' . $account->id . '.madeline');
$settings = new \danog\MadelineProto\Settings();
$m = new \danog\MadelineProto\API($sessionPath, $settings);
$m->start();

$chatIdStr = '@shbsvsvsvsx';

echo "\nTesting username...\n";
try {
    var_dump($m->getInfo($chatIdStr));
    echo "STR SUCCESS\n";
} catch (\Exception $e) {
    echo "STR ERROR: " . $e->getMessage() . "\n";
}
