<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class ManagerSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = '12345678';

    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['email' => 'manager@gmail.com'],
            [
                'name' => 'Manager User',
                'role' => 'manager',
                'password' => self::DEFAULT_PASSWORD,
                'status' => 'approved',
                'approved_at' => now(),
            ]
        );
    }
}
