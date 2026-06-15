<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\AiModel::all() as $m) {
    echo "Model: " . $m->name . " URL: " . $m->base_url . " Provider: " . $m->provider . "\n";
}
