<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Pipeline Result Check ===\n\n";

$drafts = \App\Models\Draft::orderBy('created_at', 'desc')->limit(3)->get();
echo "Recent Drafts:\n";
foreach ($drafts as $d) {
    echo "  [{$d->id}] Status: {$d->status} | Content: " . substr($d->content, 0, 80) . "\n";
}

$scheduled = \App\Models\ScheduledPost::orderBy('created_at', 'desc')->limit(3)->get();
echo "\nScheduled Posts:\n";
foreach ($scheduled as $sp) {
    echo "  [{$sp->id}] Status: {$sp->status} | scheduled_at: {$sp->scheduled_at}\n";
}

$published = \App\Models\PublishedPost::orderBy('created_at', 'desc')->limit(3)->get();
echo "\nPublished Posts:\n";
foreach ($published as $pp) {
    echo "  [{$pp->id}] telegram_message_id: {$pp->telegram_message_id} | published_at: {$pp->published_at}\n";
}

$jobs = \Illuminate\Support\Facades\DB::table('jobs')->count();
$failed = \Illuminate\Support\Facades\DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(3)->get();
echo "\nJobs in queue: {$jobs}\n";
echo "Recent failed jobs: " . count($failed) . "\n";
foreach ($failed as $f) {
    $payload = json_decode($f->payload, true);
    $jobClass = $payload['displayName'] ?? 'unknown';
    // Get exception first line
    $exception = substr($f->exception, 0, 200);
    echo "  - {$jobClass}: {$exception}\n";
}
