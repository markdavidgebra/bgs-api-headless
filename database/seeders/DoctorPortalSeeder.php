<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Medication;
use Illuminate\Database\Seeder;

class DoctorPortalSeeder extends Seeder
{
    public function run(): void
    {
        Doctor::query()->updateOrCreate(
            ['email' => 'doctor@bioglow.test'],
            [
                'name' => 'Dr. Ana Reyes',
                'password' => 'password',
                'phone' => '+63 917 000 0000',
                'specialty' => 'Aesthetic Medicine',
                'license_no' => 'PRC-1234567',
                'ptr_no' => 'PTR-2026-001',
                's2_license_no' => 'S2-0001',
                'bio' => 'Board-certified physician overseeing clinical care, vitals review, and prescribing.',
                'status' => 'active',
                'approved_at' => now(),
                'pending_password_plain' => null,
                'email_verified_at' => now(),
            ]
        );

        foreach ($this->medications() as $row) {
            Medication::query()->updateOrCreate(
                [
                    'name' => $row['name'],
                    'strength' => $row['strength'],
                    'form' => $row['form'],
                ],
                $row,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function medications(): array
    {
        $active = ['status' => 'active', 'is_controlled' => false];

        return [
            array_merge($active, ['name' => 'Biogesic', 'generic_name' => 'Paracetamol', 'strength' => '500 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Alaxan FR', 'generic_name' => 'Ibuprofen + Paracetamol', 'strength' => '200/325 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Mefenamic acid', 'generic_name' => 'Mefenamic acid', 'strength' => '500 mg', 'form' => 'capsule', 'route' => 'oral']),
            array_merge($active, ['name' => 'Celecoxib', 'generic_name' => 'Celecoxib', 'strength' => '200 mg', 'form' => 'capsule', 'route' => 'oral']),
            array_merge($active, ['name' => 'Amoxicillin', 'generic_name' => 'Amoxicillin', 'strength' => '500 mg', 'form' => 'capsule', 'route' => 'oral']),
            array_merge($active, ['name' => 'Co-amoxiclav', 'generic_name' => 'Amoxicillin + Clavulanate', 'strength' => '625 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Cefuroxime', 'generic_name' => 'Cefuroxime', 'strength' => '500 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Azithromycin', 'generic_name' => 'Azithromycin', 'strength' => '500 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Ciprofloxacin', 'generic_name' => 'Ciprofloxacin', 'strength' => '500 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Cetirizine', 'generic_name' => 'Cetirizine', 'strength' => '10 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Loratadine', 'generic_name' => 'Loratadine', 'strength' => '10 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Diphenhydramine', 'generic_name' => 'Diphenhydramine', 'strength' => '50 mg', 'form' => 'capsule', 'route' => 'oral']),
            array_merge($active, ['name' => 'Ondansetron', 'generic_name' => 'Ondansetron', 'strength' => '8 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Metoclopramide', 'generic_name' => 'Metoclopramide', 'strength' => '10 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Prednisone', 'generic_name' => 'Prednisone', 'strength' => '10 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Hydrocortisone cream', 'generic_name' => 'Hydrocortisone', 'strength' => '1%', 'form' => 'cream', 'route' => 'topical']),
            array_merge($active, ['name' => 'Mometasone cream', 'generic_name' => 'Mometasone furoate', 'strength' => '0.1%', 'form' => 'cream', 'route' => 'topical']),
            array_merge($active, ['name' => 'Mupirocin ointment', 'generic_name' => 'Mupirocin', 'strength' => '2%', 'form' => 'ointment', 'route' => 'topical']),
            array_merge($active, ['name' => 'Fusidic acid cream', 'generic_name' => 'Fusidic acid', 'strength' => '2%', 'form' => 'cream', 'route' => 'topical']),
            array_merge($active, ['name' => 'Lidocaine', 'generic_name' => 'Lidocaine', 'strength' => '2%', 'form' => 'vial', 'route' => 'SC', 'is_controlled' => false]),
            array_merge($active, ['name' => 'Lidocaine + epinephrine', 'generic_name' => 'Lidocaine + epinephrine', 'strength' => '2% + 1:100000', 'form' => 'vial', 'route' => 'SC']),
            array_merge($active, ['name' => 'Vitamin C IV', 'generic_name' => 'Ascorbic acid', 'strength' => '10 g', 'form' => 'ampoule', 'route' => 'IV']),
            array_merge($active, ['name' => 'Glutathione IV', 'generic_name' => 'Glutathione', 'strength' => '1200 mg', 'form' => 'vial', 'route' => 'IV']),
            array_merge($active, ['name' => 'Normal saline', 'generic_name' => 'Sodium chloride', 'strength' => '0.9%', 'form' => 'bag', 'route' => 'IV']),
            array_merge($active, ['name' => 'Tranexamic acid', 'generic_name' => 'Tranexamic acid', 'strength' => '500 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Omeprazole', 'generic_name' => 'Omeprazole', 'strength' => '40 mg', 'form' => 'capsule', 'route' => 'oral']),
            array_merge($active, ['name' => 'Hyoscine', 'generic_name' => 'Hyoscine butylbromide', 'strength' => '10 mg', 'form' => 'tablet', 'route' => 'oral']),
            array_merge($active, ['name' => 'Dexamethasone', 'generic_name' => 'Dexamethasone', 'strength' => '5 mg/mL', 'form' => 'ampoule', 'route' => 'IV']),
        ];
    }
}
