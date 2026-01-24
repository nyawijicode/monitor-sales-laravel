<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CustomerStatus;

echo "Searching for statuses...\n";

$statuses = CustomerStatus::all();

echo "Available Statuses:\n";
foreach ($statuses as $status) {
    echo "ID: {$status->id} - Name: {$status->name}\n";
}
