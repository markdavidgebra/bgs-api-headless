<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->text('consent_letter')->nullable()->after('reaction_md');
            $table->timestamp('consent_sent_at')->nullable()->after('consent_letter');
            $table->longText('consent_signature_data')->nullable()->after('consent_sent_at');
            $table->timestamp('consent_signed_at')->nullable()->after('consent_signature_data');
            $table->string('consent_signer_name', 255)->nullable()->after('consent_signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->dropColumn([
                'consent_letter',
                'consent_sent_at',
                'consent_signature_data',
                'consent_signed_at',
                'consent_signer_name',
            ]);
        });
    }
};
