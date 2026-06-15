<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\Draft::all() as $d) {
    echo "ID: " . $d->id . " Status: " . $d->status . " Reason: " . $d->fail_reason . "\n";
}
