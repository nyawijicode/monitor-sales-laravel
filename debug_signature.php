<?php

use App\Models\UserInfo;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$info = UserInfo::where('user_id', 18)->first();
echo "Signature for User 18: [";
echo $info ? $info->signature : "NULL";
echo "]\n";

if ($info && $info->signature) {
    echo "Raw value type: " . gettype($info->signature) . "\n";
    echo "Is JSON array? " . (str_starts_with($info->signature, '["') ? "YES" : "NO") . "\n";

    // Check file existence
    $path = public_path('storage/' . $info->signature);
    echo "Public Path check: " . $path . " -> " . (file_exists($path) ? "EXISTS" : "MISSING") . "\n";

    $storagePath = storage_path('app/public/' . $info->signature);
    echo "Storage Path check: " . $storagePath . " -> " . (file_exists($storagePath) ? "EXISTS" : "MISSING") . "\n";
}
