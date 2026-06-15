<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $rule = \App\Models\Rule::first();
    if (!$rule) {
        echo "No rules found\n";
        exit;
    }
    $job = new \App\Jobs\GenerateContentJob($rule);
    $job->handle(app(\App\Services\AI\PromptBuilderService::class));
    echo "Success!\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
