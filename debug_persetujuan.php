<?php

use App\Models\Persetujuan;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = Persetujuan::first();
if ($p) {
    echo "Raw Approvers Data:\n";
    var_dump($p->getAttributes()['approvers']); // Get raw attribute

    echo "\nCast Approvers Data:\n";
    var_dump($p->approvers); // Get cast attribute
} else {
    echo "No Persetujuan record found.\n";
}
