<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\TreatmentPackage;
use App\Models\TreatmentPatientPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TreatmentPatientPackage>
 */
class TreatmentPatientPackageFactory extends Factory
{
    protected $model = TreatmentPatientPackage::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(3, 12);
        $used = fake()->numberBetween(0, $total);
        $remaining = $total - $used;

        return [
            'patient_id' => Patient::factory(),
            'treatment_package_id' => TreatmentPackage::factory(),
            'purchased_at' => fake()->date(),
            'start_date' => fake()->optional()->date(),
            'end_date' => fake()->optional()->date(),
            'status' => fake()->randomElement(['active', 'expired', 'cancelled']),
            'total_sessions' => $total,
            'used_sessions' => $used,
            'remaining_sessions' => $remaining,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
