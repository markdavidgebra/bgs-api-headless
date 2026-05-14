<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('appointment_notes', 'aqualyx_image_path')) {
            return;
        }

        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->string('aqualyx_image_path', 512)->nullable()->after('lemon_bottle_image_path');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('appointment_notes', 'aqualyx_image_path')) {
            return;
        }

        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->dropColumn('aqualyx_image_path');
        });
    }
};
