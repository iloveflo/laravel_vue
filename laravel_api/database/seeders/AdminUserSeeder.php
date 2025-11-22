<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo admin mẫu cho demo
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'full_name' => 'Admin Demo',
                'phone' => '0123456789',
                'role' => 'admin',
            ]
        );

        $this->command->info('Admin user created: admin@example.com / password123');
    }
}
