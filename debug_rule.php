<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rule = \App\Models\Rule::find(1);
echo "Rule: {$rule->name}\n";
echo "Is Active: " . ($rule->is_active ? 'yes' : 'no') . "\n";
echo "Next Run At: {$rule->next_run_at}\n";
echo "Active From: " . ($rule->active_from ? $rule->active_from->format('H:i') : 'not set') . "\n";
echo "Active Until: " . ($rule->active_until ? $rule->active_until->format('H:i') : 'not set') . "\n";
echo "Days Active: " . json_encode($rule->days_active) . "\n";
echo "Max Per Day: {$rule->max_per_day}\n";
echo "Frequency: {$rule->frequency}\n";
echo "Current time (Dhaka): " . now()->timezone('Asia/Dhaka')->format('D H:i:s') . "\n";

// Test time window
$timezone = $rule->timezone ?? 'Asia/Dhaka';
$now = now()->setTimezone($timezone);
echo "\nNow in timezone ({$timezone}): " . $now->format('D H:i:s') . "\n";

if ($rule->active_from && $rule->active_until) {
    $from = $rule->active_from instanceof \Carbon\Carbon ? $rule->active_from->format('H:i:s') : $rule->active_from;
    $until = $rule->active_until instanceof \Carbon\Carbon ? $rule->active_until->format('H:i:s') : $rule->active_until;
    echo "Time window: {$from} - {$until}\n";
} else {
    echo "No time window set\n";
}

// Today's drafts
$todayCount = \App\Models\Draft::where('rule_id', $rule->id)
    ->whereDate('created_at', today())
    ->whereNotIn('status', ['failed', 'archived'])
    ->count();
echo "\nToday's drafts (non-failed): {$todayCount} / {$rule->max_per_day}\n";
