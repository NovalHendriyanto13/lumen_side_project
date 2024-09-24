<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;

class Maskapai extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('maskapai')->insert([[
            'code' => 'MS-'. Helper::generateRandomString(3),
            'nama' => 'Qatar'
        ], [
            'code' => 'MS-'. Helper::generateRandomString(3),
            'nama' => 'Korea'
        ], [
            'code' => 'MS-'. Helper::generateRandomString(3),
            'nama' => 'Traveloka'
        ], [
            'code' => 'MS-'. Helper::generateRandomString(3),
            'nama' => 'Mr.Aladdin'
        ], [
            'code' => 'MS-'. Helper::generateRandomString(3),
            'nama' => 'Agoda'
        ], [
            'code' => 'MS-'. Helper::generateRandomString(3),
            'nama' => 'Tiket.com'
        ], [
            'code' => 'MS-'. Helper::generateRandomString(3),
            'nama' => 'Trivago'
        ]]);
    }
}
