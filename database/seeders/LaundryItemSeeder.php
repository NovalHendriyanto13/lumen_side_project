<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaundryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('laundry_item')->insert([[
            'nama' => 'Seragam',
            'id_item' => 'MP-001'
        ], [
            'nama' => 'Celana Seragam',
            'id_item' => 'MP-002'
        ], [
            'nama' => 'Dress',
            'id_item' => 'MP-003'
        ], [
            'nama' => 'Baju Pesta',
            'id_item' => 'MP-004'
        ]]);
    }
}
