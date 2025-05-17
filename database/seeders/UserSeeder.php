<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $adminRole = Role::firstWhere('name', 'admin');
        if ($adminRole) {
            $adminUser->assignRole($adminRole);
        } else {
            $this->command->warn('Admin role not found. Please run RolePermissionSeeder first.');
        }



        $userRole = Role::firstWhere('name', 'user');
        if (!$userRole) {
            $this->command->warn('User role not found. Please run RolePermissionSeeder first.');
        }

        if ($userRole) {
            User::factory()->count(5)->create()->each(function ($user) use ($userRole) {
                $user->assignRole($userRole);
            });
        }

        $this->command->info('Admin and sample users seeded successfully!');
    }
}