<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$cols = DB::select('SHOW COLUMNS FROM orden_produccion_global');
foreach ($cols as $col) {
    echo $col->Field . ': ' . $col->Type . PHP_EOL;
}
