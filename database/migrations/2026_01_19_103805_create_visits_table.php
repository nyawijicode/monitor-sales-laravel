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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_number', 20)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('visit_plan');
            $table->date('visit_date')->nullable();
            $table->foreignId('status_awal')->constrained('customer_statuses');
            $table->foreignId('status_akhir')->nullable()->constrained('customer_statuses');
            $table->foreignId('activity_id')->nullable()->constrained('activity_types');
            $table->string('photo')->nullable();
            $table->boolean('is_join_visit')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
