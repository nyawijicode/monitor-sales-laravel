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
        Schema::table('persetujuans', function (Blueprint $table) {
            // Drop Foreign Key Constraints first to release the index dependency
            $table->dropForeign(['user_id']);
            $table->dropForeign(['company_id']);

            // Drop valid existing unique index
            // Warning: index name depends on Laravel's convention (table_columns_unique)
            $table->dropUnique('persetujuans_user_id_company_id_unique');

            // Add new unique index including 'name' (Type)
            $table->unique(['user_id', 'company_id', 'name'], 'persetujuans_user_company_name_unique');

            // Restore Foreign Key Constraints
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persetujuans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['company_id']);

            $table->dropUnique('persetujuans_user_company_name_unique');
            $table->unique(['user_id', 'company_id'], 'persetujuans_user_id_company_id_unique');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }
};
