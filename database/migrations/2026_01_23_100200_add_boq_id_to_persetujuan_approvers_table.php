<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add boq_id column to persetujuan_approvers table
        Schema::table('persetujuan_approvers', function (Blueprint $table) {
            $table->foreignId('boq_id')->nullable()->after('persetujuan_id')->constrained('boqs')->cascadeOnDelete();
        });

        // Clone approvers for each existing BOQ
        // Each BOQ will have its own set of approvers with independent status
        DB::statement("
            INSERT INTO persetujuan_approvers (persetujuan_id, boq_id, user_id, sort_order, status, notes, action_at, created_at, updated_at)
            SELECT 
                pa.persetujuan_id, 
                b.id as boq_id, 
                pa.user_id, 
                pa.sort_order, 
                pa.status, 
                pa.notes, 
                pa.action_at, 
                pa.created_at, 
                pa.updated_at
            FROM boqs b
            INNER JOIN persetujuan_approvers pa ON pa.persetujuan_id = b.persetujuan_id
            WHERE pa.boq_id IS NULL
        ");

        // Delete old template approvers (those without boq_id)
        // We keep template approvers for future BOQs, so DON'T delete them
        // DB::table('persetujuan_approvers')->whereNull('boq_id')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete BOQ-specific approvers (those with boq_id)
        DB::table('persetujuan_approvers')->whereNotNull('boq_id')->delete();

        // Remove boq_id column
        Schema::table('persetujuan_approvers', function (Blueprint $table) {
            $table->dropForeign(['boq_id']);
            $table->dropColumn('boq_id');
        });
    }
};
