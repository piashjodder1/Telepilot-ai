<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// active_until বাড়িয়ে দিই যাতে এখন কাজ করে
\App\Models\Rule::find(1)->update([
    'active_from'  => '09:00:00',
    'active_until' => '23:00:00',
    'next_run_at'  => null,
]);

echo "Rule updated: active 09:00-23:00\n";
