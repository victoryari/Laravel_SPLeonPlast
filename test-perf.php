<?php
$start = microtime(true);
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

Illuminate\Support\Facades\Event::listen("*", function($eventName, $data) use ($start) {
    if (is_string($eventName)) {
        if (strpos($eventName, "Illuminate\Foundation\Http\Events\RequestHandled") !== false) return;
        echo round((microtime(true) - $start) * 1000, 2) . " ms - Event: $eventName\n";
    }
});

$request = Illuminate\Http\Request::create("/login", "GET");
$response = $kernel->handle($request);

