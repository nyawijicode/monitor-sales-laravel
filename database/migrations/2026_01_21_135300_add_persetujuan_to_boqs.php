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
            $table->foreignId('persetujuan_id')->nullable()->after('user_id')->constrained('persetujuans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            $table->dropForeign(['persetujuan_id']);
            $table->dropColumn('persetujuan_id');
        });
    }
};
