<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_patient_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('treatment_package_id')->constrained()->cascadeOnDelete();
            $table->date('purchased_at')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->integer('total_sessions')->default(0);
            $table->integer('used_sessions')->default(0);
            $table->integer('remaining_sessions')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_patient_package');
    }
};
