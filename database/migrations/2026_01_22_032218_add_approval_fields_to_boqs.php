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
        Schema::table('boqs', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('total_amount');
            $table->text('approval_notes')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approval_notes');
            $table->foreignId('approved_by')->nullable()->after('approved_at')
                ->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approval_notes', 'approved_at', 'approved_by']);
        });
    }
};
