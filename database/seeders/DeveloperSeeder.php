<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class DeveloperSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = '12345678';

    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['email' => 'developer@gmail.com'],
            [
                'name' => 'Developer',
                'role' => 'developer',
                'password' => self::DEFAULT_PASSWORD,
                'status' => 'approved',
                'approved_at' => now(),
            ]
        );
    }
}
