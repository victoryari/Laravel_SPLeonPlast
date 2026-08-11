<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Merma;
use Illuminate\Support\Facades\DB;

$mermas = Merma::all();
$updated = 0;

foreach ($mermas as $m) {
    $oldNumero = "MERMA-" . str_pad($m->id_merma, 6, "0", STR_PAD_LEFT);
    
    $tipo = strtoupper($m->tipo_merma ?? "");
    $motivo = strtolower($m->motivo ?? "");

    if ($tipo === "MOLIDO") {
        $newNumero = "MOLIDO-" . str_pad($m->id_merma, 6, "0", STR_PAD_LEFT);
    } elseif ($tipo === "LIMPIEZA" || $tipo === "MIXTO" || strpos($motivo, "limpieza") !== false) {
        $newNumero = "LIMPIEZA-" . str_pad($m->id_merma, 6, "0", STR_PAD_LEFT);
    } elseif ($tipo === "RECUPERABLE") {
        $newNumero = "RECICLAD-" . str_pad($m->id_merma, 6, "0", STR_PAD_LEFT);
    } else {
        $newNumero = $oldNumero;
    }

    if ($oldNumero !== $newNumero) {
        // Update in kardex (regular and ANUL)
        DB::table("kardex")->where("numero_documento", $oldNumero)->update(["numero_documento" => $newNumero]);
        DB::table("kardex")->where("numero_documento", "ANUL-" . $oldNumero)->update(["numero_documento" => "ANUL-" . $newNumero]);
        $updated++;
    }
}

echo "Mermas actualizadas: $updated\n";

