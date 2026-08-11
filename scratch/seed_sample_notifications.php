<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Notificacion;
use App\Models\Usuario;

$admin = Usuario::where('nombre_usuario', 'admin')->first();
$adminId = $admin ? $admin->id_usuario : null;

// Crear notificaciones de bienvenida y prueba si no existen
if ($adminId) {
    Notificacion::firstOrCreate([
        'id_usuario' => $adminId,
        'titulo'     => '¡Bienvenido al Gestor de Notificaciones!',
        'mensaje'    => 'Ahora puedes recibir alertas en tiempo real sobre órdenes de producción, stock e inventario.',
        'tipo'       => 'info',
        'link'       => route('dashboard'),
    ]);

    Notificacion::firstOrCreate([
        'id_usuario' => $adminId,
        'titulo'     => 'Sistema Actualizado',
        'mensaje'    => 'La barra superior y el módulo de notificaciones con campanita han sido integrados con éxito.',
        'tipo'       => 'success',
        'link'       => route('notificaciones.index'),
    ]);

    echo "Notificaciones de prueba creadas con éxito para el usuario admin (ID {$adminId}).\n";
} else {
    echo "No se encontró el usuario admin para asociar la notificación.\n";
}
