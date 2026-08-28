<?php

use App\Models\AppointmentNote;
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
            $table->json('vital_signs')->nullable()->after('vital_height');
        });

        AppointmentNote::query()->orderBy('id')->chunkById(100, function ($notes): void {
            foreach ($notes as $note) {
                $phased = $note->resolvedVitalSigns();
                if (! AppointmentNote::vitalSignsHaveValues($phased)) {
                    continue;
                }
                $note->vital_signs = $phased;
                $note->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_notes', function (Blueprint $table) {
            $table->dropColumn('vital_signs');
        });
    }
};
