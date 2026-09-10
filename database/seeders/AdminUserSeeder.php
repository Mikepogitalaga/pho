<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'        => 'Admin',
                'employee_id' => 'EMP-001',
                'address'     => 'N/A',
                'password'    => bcrypt('password'),
                'role'        => User::ROLE_ADMIN,
                'is_active'   => true,
            ]
        );
    }
}
