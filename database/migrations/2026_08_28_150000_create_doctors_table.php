<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The prescribing Doctor portal guard. Unrelated to the legacy `doctors`
     * table that older migrations create — that one was renamed to
     * `clinical_staff`, which frees this name for the real doctor.
     */
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique('doctors_email_uq');
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('specialty')->nullable();
            $table->string('license_no')->nullable();
            $table->date('prc_expiry')->nullable();
            $table->string('ptr_no')->nullable();
            $table->string('s2_license_no')->nullable();
            $table->string('signature_path', 512)->nullable();
            $table->text('bio')->nullable();
            $table->string('image_path', 512)->nullable();
            $table->string('status', 32)->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->text('pending_password_plain')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('status', 'doctors_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
