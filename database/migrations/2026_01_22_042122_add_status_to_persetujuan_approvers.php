<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add status tracking to persetujuan_approvers table
        Schema::table('persetujuan_approvers', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('sort_order');
            $table->text('notes')->nullable()->after('status');
            $table->timestamp('action_at')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persetujuan_approvers', function (Blueprint $table) {
            $table->dropColumn(['status', 'notes', 'action_at']);
        });
    }
};
