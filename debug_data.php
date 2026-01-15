<?php

use App\Models\Province;
use App\Models\City;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DEBUG START ---\n";
// Check Province 33 (Should be Jawa Tengah)
$p33 = Province::find(33);
echo "Province 33: " . ($p33 ? $p33->name : "NOT FOUND") . "\n";

// Check Province 91 (Should be Papua Barat)
$p91 = Province::find(91);
echo "Province 91: " . ($p91 ? $p91->name : "NOT FOUND") . "\n";

// Check Semarang City
$semarang = City::where('name', 'LIKE', '%SEMARANG%')->first();
if ($semarang) {
    echo "Semarang City: " . $semarang->name . "\n";
    echo "Semarang Province ID: " . $semarang->province_id . "\n";
    $prov = Province::find($semarang->province_id);
    echo "Linked Province Name: " . ($prov ? $prov->name : "NOT FOUND") . "\n";
} else {
    echo "Semarang City NOT FOUND\n";
}

// Check what ID 'Jawa Tengah' actually has
$jateng = Province::where('name', 'LIKE', '%JAWA TENGAH%')->first();
echo "Jawa Tengah actual ID: " . ($jateng ? $jateng->id : "NOT FOUND") . "\n";

echo "--- DEBUG END ---\n";
