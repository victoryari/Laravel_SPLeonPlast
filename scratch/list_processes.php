<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$procesos = DB::table('proceso_produccion')->get();
foreach($procesos as $p) {
    echo "{$p->codigo} | {$p->descripcion}\n";
}
