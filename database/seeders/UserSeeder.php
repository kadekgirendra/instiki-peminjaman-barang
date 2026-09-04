<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([

            'username' => 'admin',
            'name' => 'Administrator',
            'nim_nidn' => null,
            'password' => 'giren&yordi',
            'role' => 'admin',
        ]);
    }
}
