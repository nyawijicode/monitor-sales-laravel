<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

echo "Starting manual migration check...\n";

// 1. Add boq_id column if missing
if (!Schema::hasColumn('persetujuan_approvers', 'boq_id')) {
    echo "Column boq_id missing. Adding it...\n";
    Schema::table('persetujuan_approvers', function (Blueprint $table) {
        $table->foreignId('boq_id')->nullable()->after('persetujuan_id');
        // We skip constraint for now to avoid issues, or simpler:
        // $table->foreign('boq_id')->references('id')->on('boqs')->onDelete('cascade');
    });
    echo "Column added.\n";
} else {
    echo "Column boq_id already exists.\n";
}

// 2. Data Migration: Clone Approvers
echo "Running data migration (cloning approvers)...\n";

$query = "
    INSERT INTO persetujuan_approvers (persetujuan_id, boq_id, user_id, sort_order, status, notes, action_at, created_at, updated_at)
    SELECT 
        pa.persetujuan_id, 
        b.id as boq_id, 
        pa.user_id, 
        pa.sort_order, 
        pa.status, 
        pa.notes, 
        pa.action_at, 
        COALESCE(pa.created_at, NOW()), 
        COALESCE(pa.updated_at, NOW())
    FROM boqs b
    INNER JOIN persetujuan_approvers pa ON pa.persetujuan_id = b.persetujuan_id
    WHERE pa.boq_id IS NULL
    AND NOT EXISTS (
        SELECT 1 FROM persetujuan_approvers existing 
        WHERE existing.boq_id = b.id 
        AND existing.user_id = pa.user_id
    )
";

$affected = DB::statement($query);

echo "Data migration completed.\n";
