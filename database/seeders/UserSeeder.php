<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@pom.go.id'],
            [
                'name' => 'Yuli Supriyatno',
                'role' => 'Operator',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'distribusi@pom.go.id'],
            [
                'name' => 'Petugas Pengaju',
                'role' => 'User',
                'password' => Hash::make('password'),
            ]
        );
    }
}