<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = \App\Models\Rule::find(1);
echo 'Next Run At: ' . $r->next_run_at . "\n";
echo 'Frequency: ' . $r->frequency . "\n";
echo 'Custom Mins: ' . $r->custom_minutes . "\n";
echo 'Last Run: ' . $r->last_run_at . "\n";
