<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = App\Models\Channel::find(20);
if ($c) {
    print_r($c->toArray());
} else {
    echo "Channel 20 not found.\n";
}
