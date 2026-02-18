<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'gedeagusadityadharma@gmail.com'],
            [
                'password' => Hash::make('@Gd03072003'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');

        $admin->profile()->updateOrCreate(
            [
                'full_name' => 'Aditya Dharma',
                'is_verified' => true,
            ]
        );
    }
}
