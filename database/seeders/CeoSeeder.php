<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class CeoSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = '12345678';

    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['email' => 'ceo@gmail.com'],
            [
                'name' => 'CEO',
                'role' => 'ceo',
                'password' => self::DEFAULT_PASSWORD,
                'status' => 'approved',
                'approved_at' => now(),
            ]
        );
    }
}
