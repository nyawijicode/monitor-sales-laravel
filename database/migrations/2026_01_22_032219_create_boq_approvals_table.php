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
        Schema::create('boq_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boq_id')->constrained('boqs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['approved', 'rejected', 'reset']);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index untuk query performance
            $table->index(['boq_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boq_approvals');
    }
};
