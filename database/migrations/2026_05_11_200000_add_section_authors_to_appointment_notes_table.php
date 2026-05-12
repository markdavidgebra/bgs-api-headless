<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->json('section_authors')->nullable()->after('alerts');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->dropColumn('section_authors');
        });
    }
};
