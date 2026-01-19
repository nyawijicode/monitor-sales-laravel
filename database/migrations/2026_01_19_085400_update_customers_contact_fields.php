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
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('kontak');
            $table->string('nama_kontak')->nullable()->after('alamat');
            $table->string('telepon', 20)->nullable()->after('nama_kontak');
            $table->string('jabatan')->nullable()->after('telepon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['nama_kontak', 'telepon', 'jabatan']);
            $table->string('kontak', 50)->nullable()->after('alamat');
        });
    }
};
