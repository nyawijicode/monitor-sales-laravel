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
        // 1. Add after_sales_id to persetujuan_approvers
        Schema::table('persetujuan_approvers', function (Blueprint $table) {
            $table->foreignId('after_sales_id')->nullable()->constrained('after_sales')->cascadeOnDelete()->after('sales_order_id');
        });

        // 2. Add persetujuan_id to after_sales
        Schema::table('after_sales', function (Blueprint $table) {
            $table->foreignId('persetujuan_id')->nullable()->constrained('persetujuans')->nullOnDelete()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('after_sales', function (Blueprint $table) {
            $table->dropForeign(['persetujuan_id']);
            $table->dropColumn('persetujuan_id');
        });

        Schema::table('persetujuan_approvers', function (Blueprint $table) {
            $table->dropForeign(['after_sales_id']);
            $table->dropColumn('after_sales_id');
        });
    }
};
