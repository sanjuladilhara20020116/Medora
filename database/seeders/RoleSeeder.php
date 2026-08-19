<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'ADMIN',
                'description' => 'Full access to all hospital management system modules.',
                'is_active' => true,
            ],

            [
                'name' => 'Doctor',
                'slug' => 'DOCTOR',
                'description' => 'Access to patients, appointments, medical records, prescriptions, and laboratory requests.',
                'is_active' => true,
            ],

            [
                'name' => 'Nurse',
                'slug' => 'NURSE',
                'description' => 'Access to patient care information and assigned clinical activities.',
                'is_active' => true,
            ],

            [
                'name' => 'Receptionist',
                'slug' => 'RECEPTIONIST',
                'description' => 'Access to patient registration and appointment management.',
                'is_active' => true,
            ],

            [
                'name' => 'Laboratory Staff',
                'slug' => 'LAB_STAFF',
                'description' => 'Access to laboratory requests, samples, test processing, and results.',
                'is_active' => true,
            ],

            [
                'name' => 'Pharmacist',
                'slug' => 'PHARMACIST',
                'description' => 'Access to prescriptions, medicines, inventory, and pharmacy stock.',
                'is_active' => true,
            ],

            [
                'name' => 'Accountant',
                'slug' => 'ACCOUNTANT',
                'description' => 'Access to invoices, payments, billing, and financial reports.',
                'is_active' => true,
            ],

            [
                'name' => 'Patient',
                'slug' => 'PATIENT',
                'description' => 'Access to the patient portal and the patient’s own profile information.',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                [
                    'slug' => $role['slug'],
                ],
                $role
            );
        }
    }
}
