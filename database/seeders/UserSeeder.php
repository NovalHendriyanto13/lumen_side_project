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
            'nik' => '30088019548801',
            'nama' => 'Admin',
            'username' => 'admin@example.com',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'no_telp' => '081111111',
            'alamat' => 'Jl. Cendana Tangerang',
            'role' => 'admin',
        ], [
            'nik' => '30088019548802',
            'nama' => 'Petugas Sekretaris',
            'username' => 'petugas@example.com',
            'email' => 'petugas@example.com',
            'password' => Hash::make('password'),
            'no_telp' => '08222222',
            'alamat' => 'Jl. Mahoni Tangerang',
            'role' => 'petugas',
        ], [
            'nik' => '30088019548803',
            'nama' => 'User Doe',
            'username' => 'user@example.com',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'no_telp' => '08333333',
            'alamat' => 'Jl. Jati Tangerang',
            'role' => 'user',
        ]]);
    }
}
