<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rule = \App\Models\Rule::find(2); // assuming the new rule is ID 2
if (!$rule) $rule = \App\Models\Rule::latest()->first();

echo "Rule ID: {$rule->id}\n";
echo "Active From: {$rule->active_from}\n";
echo "Active Until: {$rule->active_until}\n";

$timezone   = $rule->timezone ?? 'Asia/Dhaka';
$now        = now()->setTimezone($timezone);
$activeFrom = \Carbon\Carbon::createFromTimeString($rule->active_from instanceof \Carbon\Carbon ? $rule->active_from->format('H:i:s') : $rule->active_from, $timezone);
$activeUntil = \Carbon\Carbon::createFromTimeString($rule->active_until instanceof \Carbon\Carbon ? $rule->active_until->format('H:i:s') : $rule->active_until, $timezone);

$activeFrom  = $now->copy()->setTimeFrom($activeFrom);
$activeUntil = $now->copy()->setTimeFrom($activeUntil);

echo "Now: " . $now->format('H:i:s') . "\n";
echo "From Today: " . $activeFrom->format('H:i:s') . "\n";
echo "Until Today: " . $activeUntil->format('H:i:s') . "\n";

$isWithin = false;
if ($activeFrom->greaterThan($activeUntil)) {
    // Crosses midnight
    $isWithin = clone $now >= clone $activeFrom || clone $now <= clone $activeUntil;
} else {
    // Normal window
    $isWithin = clone $now >= clone $activeFrom && clone $now <= clone $activeUntil;
}

echo "Is Within Window? " . ($isWithin ? 'YES' : 'NO') . "\n";
