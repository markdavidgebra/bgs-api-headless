<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('appointment_notes', 'micro_needling_image_path')) {
            return;
        }

        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->string('micro_needling_image_path', 512)->nullable()->after('drip_image_path');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('appointment_notes', 'micro_needling_image_path')) {
            return;
        }

        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->dropColumn('micro_needling_image_path');
        });
    }
};
