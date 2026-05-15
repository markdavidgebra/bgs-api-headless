<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class InventoryOfficerSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = '12345678';

    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['email' => 'inventory@gmail.com'],
            [
                'name' => 'Inventory Officer',
                'role' => 'inventory_officer',
                'status' => 'approved',
                'approved_at' => now(),
                'password' => self::DEFAULT_PASSWORD,
            ]
        );
    }
}
