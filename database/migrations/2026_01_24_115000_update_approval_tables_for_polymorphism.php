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
        // 1. Add name/type to persetujuans table to differentiate templates
        Schema::table('persetujuans', function (Blueprint $table) {
            $table->string('name')->default('BOQ')->after('id'); // e.g. "BOQ", "Sales Order"
        });

        // 2. Add sales_order_id to persetujuan_approvers to support SO approvals
        Schema::table('persetujuan_approvers', function (Blueprint $table) {
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders')->cascadeOnDelete()->after('boq_id');
        });

        // 3. Add persetujuan_id to sales_orders to track which template was used
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('persetujuan_id')->nullable()->constrained('persetujuans')->nullOnDelete()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['persetujuan_id']);
            $table->dropColumn('persetujuan_id');
        });

        Schema::table('persetujuan_approvers', function (Blueprint $table) {
            $table->dropForeign(['sales_order_id']);
            $table->dropColumn('sales_order_id');
        });

        Schema::table('persetujuans', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
