<?php
$desc = 'COLOR MASTERBATCH ORANGE FLOUR CMO-61089';
$esPigmento = str_contains($desc, 'COLOR') || str_contains($desc, 'MASTERBATCH') || str_contains($desc, 'PIGMENTO');
var_dump($esPigmento);
