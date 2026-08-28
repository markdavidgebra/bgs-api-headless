<?php

namespace Database\Factories;

use App\Models\ClinicalStaff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClinicalStaff>
 */
class ClinicalStaffFactory extends Factory
{
    protected $model = ClinicalStaff::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'specialty' => fake()->randomElement(['Dermatology', 'General Practice', 'Aesthetic Medicine']),
            'license_no' => 'LIC-'.fake()->numerify('####'),
            'experience_years' => fake()->numberBetween(1, 25),
            'bio' => fake()->sentence(12),
            'image_path' => null,
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
