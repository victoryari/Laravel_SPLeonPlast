<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$ks = app(App\Services\KardexService::class);
$ks->recalcular('C200_Z002', 'PS5H');
echo 'Done';
