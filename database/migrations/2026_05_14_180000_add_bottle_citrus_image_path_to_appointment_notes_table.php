<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->string('bottle_citrus_image_path', 512)->nullable()->after('body_analyzer_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->dropColumn('bottle_citrus_image_path');
        });
    }
};
