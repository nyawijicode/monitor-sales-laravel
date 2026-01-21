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
        Schema::create('boq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boq_id')->constrained()->onDelete('cascade');
            $table->string('nama_barang');
            $table->integer('qty');
            $table->decimal('harga_barang', 15, 2);
            $table->decimal('harga_penawaran', 15, 2)->nullable();
            $table->text('spesifikasi')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boq_items');
    }
};
