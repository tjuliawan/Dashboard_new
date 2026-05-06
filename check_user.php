<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$email = 'muhamadaliridwan18@gmail.com';

$rows = DB::connection('rcm_hgs_dummy')->select(
    "SELECT * FROM users WHERE email = ?",
    [$email]
);

echo "=== rcm_hgs_dummy user row ===\n";
print_r($rows);

// Show columns of users table
$cols = DB::connection('rcm_hgs_dummy')->select(
    "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' ORDER BY ORDINAL_POSITION"
);
echo "\n=== users columns (rcm_hgs_dummy) ===\n";
foreach ($cols as $c) {
    echo "  - {$c->COLUMN_NAME} ({$c->DATA_TYPE}) nullable={$c->IS_NULLABLE}\n";
}
