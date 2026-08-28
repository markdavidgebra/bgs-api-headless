<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Doctor notes live here rather than in `appointment_notes` so a doctor's note is
     * always distinguishable from a clinical staff note, and so a doctor can write about
     * a patient without being tied to exactly one appointment row.
     */
    public function up(): void
    {
        Schema::create('doctor_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users', 'id', 'dr_note_patient_fk')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors', 'id', 'dr_note_doctor_fk')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments', 'id', 'dr_note_appointment_fk')->nullOnDelete();
            $table->text('note');
            $table->text('diagnosis')->nullable();
            $table->text('plan')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'created_at'], 'dr_note_patient_created_idx');
            $table->index(['doctor_id', 'created_at'], 'dr_note_doctor_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_notes');
    }
};
