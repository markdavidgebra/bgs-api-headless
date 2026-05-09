<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Default password for local / demo environments (all seeded admins, including head nurse).
     */
    private const DEFAULT_PASSWORD = '12345678';

    public function run(): void
    {
        $admins = [
            [
                'email' => 'admin@gmail.com',
                'name' => 'Admin User',
                'role' => 'admin',
            ],
            [
                'email' => 'superadmin@gmail.com',
                'name' => 'Super Admin',
                'role' => 'super admin',
            ],
            [
                'email' => 'headnurse@gmail.com',
                'name' => 'Head Nurse',
                'role' => 'admin',
            ],
        ];

        foreach ($admins as $admin) {
            Admin::query()->updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'role' => $admin['role'],
                    'password' => self::DEFAULT_PASSWORD,
                ]
            );
        }
    }
}
