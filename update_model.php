<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$m = \App\Models\AiModel::first();
if ($m) {
    $m->provider = 'opencode';
    $m->model = 'google/gemini-1.5-flash';
    $m->base_url = 'https://opencode.ai/zen/v1';
    $m->save();
}
