<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get ALL failed jobs
$failed = \Illuminate\Support\Facades\DB::table('failed_jobs')->orderBy('failed_at', 'desc')->get();
foreach ($failed as $f) {
    $payload = json_decode($f->payload, true);
    echo "=== Job: " . ($payload['displayName'] ?? 'unknown') . " ===\n";
    echo "Failed at: {$f->failed_at}\n";
    echo "Exception: " . substr($f->exception, 0, 300) . "\n\n";
}

// Also check scheduled post status
$sp = \App\Models\ScheduledPost::orderBy('created_at','desc')->first();
if ($sp) {
    echo "\n=== Latest ScheduledPost ===\n";
    echo "Status: {$sp->status}\n";
    echo "Fail Reason: {$sp->fail_reason}\n";
    echo "Draft ID: {$sp->draft_id}\n";
    $draft = $sp->draft;
    if ($draft) {
        echo "Draft Status: {$draft->status}\n";
        echo "Draft Content (100 chars): " . substr($draft->content, 0, 100) . "\n";
    }
}
