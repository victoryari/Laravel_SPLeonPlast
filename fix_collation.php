<?php
use Illuminate\Support\Facades\DB;

DB::statement('ALTER TABLE roles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;');
DB::statement('ALTER TABLE modulos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;');
DB::statement('ALTER TABLE rol_modulo CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;');
echo "Collation changed successfully.\n";
