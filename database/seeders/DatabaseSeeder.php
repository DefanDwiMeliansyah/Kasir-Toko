<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'nama'     => 'Administrator',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        \App\Models\User::create([
            'nama'     => 'Petugas',
            'username' => 'petugas',
            'password' => bcrypt('password'),
            'role'     => 'petugas',
        ]);
    }
}
