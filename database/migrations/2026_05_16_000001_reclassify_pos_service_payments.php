<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * POS walk-in service sales were stored as appointment + service id.
     */
    public function up(): void
    {
        DB::table('payments')
            ->where('reference_type', 'appointment')
            ->whereNotNull('reference_id')
            ->orderBy('id')
            ->chunkById(100, function ($payments): void {
                foreach ($payments as $payment) {
                    $referenceId = (int) $payment->reference_id;

                    $appointmentExists = DB::table('appointments')
                        ->where('id', $referenceId)
                        ->exists();

                    if ($appointmentExists) {
                        continue;
                    }

                    $serviceExists = DB::table('services')
                        ->where('id', $referenceId)
                        ->exists();

                    if ($serviceExists) {
                        DB::table('payments')
                            ->where('id', $payment->id)
                            ->update(['reference_type' => 'service']);
                    }
                }
            });
    }

    public function down(): void
    {
        // Irreversible without storing prior classification.
    }
};
