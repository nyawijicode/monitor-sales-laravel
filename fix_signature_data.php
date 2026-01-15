<?php

use App\Models\UserInfo;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$infos = UserInfo::whereNotNull('signature')->get();

foreach ($infos as $info) {
    $sig = $info->signature;
    if (str_starts_with($sig, '[') || str_starts_with($sig, '{')) {
        echo "Processing User ID {$info->user_id}: $sig\n";
        $decoded = json_decode($sig, true);
        if (is_array($decoded)) {
            // Extract first value
            $cleanPath = array_values($decoded)[0] ?? null;
            if ($cleanPath) {
                $info->update(['signature' => $cleanPath]);
                echo " -> Fixed: $cleanPath\n";
            }
        }
    } else {
        echo "User ID {$info->user_id}: Valid ($sig)\n";
    }
}
echo "Done cleaning signatures.\n";
