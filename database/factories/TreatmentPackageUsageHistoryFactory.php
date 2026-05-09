<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Service;
use App\Models\TreatmentPackageUsageHistory;
use App\Models\TreatmentPatientPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreatmentPackageUsageHistory>
 */
class TreatmentPackageUsageHistoryFactory extends Factory
{
    protected $model = TreatmentPackageUsageHistory::class;

    public function definition(): array
    {
        $patient = Patient::factory();

        return [
            'patient_package_id' => TreatmentPatientPackage::factory()->for($patient, 'patient'),
            'patient_id' => $patient,
            'service_id' => Service::factory(),
            'used_on' => fake()->date(),
            'session_change' => -1,
            'status' => 'completed',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
