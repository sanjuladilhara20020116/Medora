<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('slug', 'ADMIN')->first();

        if (!$adminRole) {
            throw new RuntimeException(
                'ADMIN role was not found. Run RoleSeeder before AdminUserSeeder.'
            );
        }

        $name = env('ADMIN_NAME');
        $username = env('ADMIN_USERNAME');
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (!$name || !$username || !$email || !$password) {
            throw new RuntimeException(
                'Administrator environment variables are missing from the .env file.'
            );
        }

        User::updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'role_id' => $adminRole->id,
                'name' => $name,
                'username' => $username,
                'phone' => null,
                'password' => Hash::make($password),
                'is_active' => true,
            ]
        );
    }
}