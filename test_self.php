<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$account = App\Models\TelegramAccount::where('login_status', 'logged_in')->first();
$sessionPath = storage_path('app/madeline/session_' . $account->id . '.madeline');
$settings = new \danog\MadelineProto\Settings();
$m = new \danog\MadelineProto\API($sessionPath, $settings);

$fullInfo = $m->getFullInfo('me');
print_r($fullInfo['User']);
