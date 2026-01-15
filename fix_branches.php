<?php

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- STARTING BRANCH ID FIX ---\n";

$usersWithBranches = DB::table('branch_user')->select('user_id', 'branch_id')->get()->groupBy('user_id');

foreach ($usersWithBranches as $userId => $branches) {
    // Take the first branch assigned
    $firstBranchId = $branches->first()->branch_id;

    echo "Processing User ID: {$userId} -> Branch ID: {$firstBranchId}... ";

    $userInfo = UserInfo::where('user_id', $userId)->first();

    if ($userInfo) {
        if (is_null($userInfo->branch_id)) {
            $userInfo->branch_id = $firstBranchId;
            $userInfo->save();
            echo "UPDATED (Was NULL)\n";
        } else {
            echo "SKIPPED (Already has branch_id: {$userInfo->branch_id})\n";
        }
    } else {
        echo "NO USER INFO FOUND\n";
        // Optionally create it? logic in resource does updateOrCreate.
        // Let's create it if missing for completeness
        UserInfo::create([
            'user_id' => $userId,
            'branch_id' => $firstBranchId,
            // Default other fields to avoid Not Null errors if any, though most are nullable or have defaults?
            // Assuming nullable based on previous context, but safer to just update existing.
        ]);
        echo "CREATED NEW USER INFO\n";
    }
}

echo "--- FIX COMPLETE ---\n";
