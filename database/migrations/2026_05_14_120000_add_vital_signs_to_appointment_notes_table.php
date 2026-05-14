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
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->string('vital_blood_pressure', 50)->nullable()->after('alerts');
            $table->string('vital_heart_rate', 32)->nullable()->after('vital_blood_pressure');
            $table->string('vital_temperature', 32)->nullable()->after('vital_heart_rate');
            $table->string('vital_respiratory_rate', 32)->nullable()->after('vital_temperature');
            $table->string('vital_oxygen_saturation', 32)->nullable()->after('vital_respiratory_rate');
            $table->string('vital_weight', 32)->nullable()->after('vital_oxygen_saturation');
            $table->string('vital_height', 32)->nullable()->after('vital_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->dropColumn([
                'vital_blood_pressure',
                'vital_heart_rate',
                'vital_temperature',
                'vital_respiratory_rate',
                'vital_oxygen_saturation',
                'vital_weight',
                'vital_height',
            ]);
        });
    }
};
