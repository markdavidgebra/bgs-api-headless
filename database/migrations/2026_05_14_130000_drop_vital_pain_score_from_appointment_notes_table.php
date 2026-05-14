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
        if (Schema::hasColumn('appointment_notes', 'vital_pain_score')) {
            Schema::table('appointment_notes', function (Blueprint $table) {
                $table->dropColumn('vital_pain_score');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->string('vital_pain_score', 16)->nullable()->after('vital_height');
        });
    }
};
