<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$prods = DB::table('producto_proceso')->get();
foreach($prods as $p) {
    echo "ID: {$p->codigo} | Desc: {$p->descripcion}\n";
}
