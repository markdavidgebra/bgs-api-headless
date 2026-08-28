<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_no', 50)->unique('prescriptions_no_uq');
            $table->foreignId('patient_id')->constrained('users', 'id', 'rx_patient_fk')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors', 'id', 'rx_doctor_fk')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments', 'id', 'rx_appointment_fk')->nullOnDelete();
            $table->dateTime('issued_at');
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['patient_id', 'issued_at'], 'rx_patient_issued_idx');
            $table->index(['doctor_id', 'status'], 'rx_doctor_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
