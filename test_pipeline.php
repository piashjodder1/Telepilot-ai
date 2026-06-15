<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== AutoPulse Full Pipeline Test ===\n\n";

// 1. Rules চেক করো
$rules = \App\Models\Rule::where('is_active', true)->get();
echo "✅ Active Rules: " . $rules->count() . "\n";
foreach ($rules as $rule) {
    echo "   - Rule [{$rule->id}]: {$rule->name} | Freq: {$rule->frequency} | next_run_at: {$rule->next_run_at}\n";
}

// 2. AI Models চেক
$models = \App\Models\AiModel::where('is_active', true)->get();
echo "\n✅ Active AI Models: " . $models->count() . "\n";
foreach ($models as $m) {
    echo "   - [{$m->id}]: {$m->name} | Provider: {$m->provider} | Model: {$m->model}\n";
}

// 3. Channels চেক
$channels = \App\Models\Channel::where('is_active', true)->get();
echo "\n✅ Active Channels: " . $channels->count() . "\n";
foreach ($channels as $ch) {
    echo "   - [{$ch->id}]: {$ch->title} | chat_id: {$ch->chat_id}\n";
}

// 4. Draft status চেক
$drafts = \App\Models\Draft::orderBy('created_at', 'desc')->limit(5)->get();
echo "\n✅ Recent Drafts: " . $drafts->count() . "\n";
foreach ($drafts as $d) {
    echo "   - [{$d->id}]: Status: {$d->status} | " . substr($d->content, 0, 60) . "...\n";
}

// 5. ScheduledPosts চেক
$scheduled = \App\Models\ScheduledPost::orderBy('created_at', 'desc')->limit(5)->get();
echo "\n✅ Recent ScheduledPosts: " . $scheduled->count() . "\n";
foreach ($scheduled as $sp) {
    echo "   - [{$sp->id}]: Status: {$sp->status} | scheduled_at: {$sp->scheduled_at}\n";
}

// 6. Heartbeat চালাও
echo "\n\n🔄 Running Heartbeat manually...\n";
$heartbeat = app(\App\Services\HeartbeatService::class);
$heartbeat->tick();
echo "✅ Heartbeat completed!\n";

// 7. Queue job আছে কিনা চেক
$jobCount = \Illuminate\Support\Facades\DB::table('jobs')->count();
echo "\n✅ Jobs in Queue: " . $jobCount . "\n";
echo "\nIf jobs > 0, run: php artisan queue:work --once\n";

echo "\n=== Test Complete ===\n";
