<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rule = App\Models\Rule::first();
App\Jobs\GenerateContentJob::dispatch($rule);
echo "Dispatched for Rule ID: " . $rule->id . "\n";
