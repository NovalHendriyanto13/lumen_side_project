<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([[
            'nama' => 'Admin',
            'username' => 'admin@example.com',
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password'),
            'no_telp' => '081111111',
            'alamat' => 'Jl. Cendana Tangerang',
            'role' => 'admin',
        ], [
            'nama' => 'Checker',
            'username' => 'checker@example.com',
            'email' => 'checker@example.com',
            'password' => Hash::make('password'),
            'no_telp' => '08222222',
            'alamat' => 'Jl. Mahoni Tangerang',
            'role' => 'checker',
        ], [
            'nama' => 'Guest Doe',
            'username' => 'guest@example.com',
            'email' => 'guset_doe@example.com',
            'password' => Hash::make('password'),
            'no_telp' => '08333333',
            'alamat' => 'Jl. Jati Tangerang',
            'role' => 'guest',
        ], [
            'nama' => 'Operator Doe',
            'username' => 'operator@example.com',
            'email' => 'operator@example.com',
            'password' => Hash::make('password'),
            'no_telp' => '08444444',
            'alamat' => 'Jl. Cemara Tangerang',
            'role' => 'operator',
        ]]);
    }
}
