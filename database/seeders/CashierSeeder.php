<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class CashierSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = '12345678';

    public function run(): void
    {
        Admin::query()->updateOrCreate(
            ['email' => 'cashier@gmail.com'],
            [
                'name' => 'Cashier User',
                'role' => 'cashier',
                'password' => self::DEFAULT_PASSWORD,
            ]
        );
    }
}
