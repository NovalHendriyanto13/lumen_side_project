<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Maskapai;

class UpdateUserCrew extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where(function($q) {
                return $q->whereNotNull('user_kru')
                    ->orWhere('user_kru', '<>', '');
            })
            ->get();

        foreach($users as $user) {
            if (!empty($user->user_kru)) {
                $maskapai = Maskapai::where('nama', $user->user_kru)->first();

                $user->user_kru = $maskapai->id;
                $user->save();
            } else {
                $user->user_kru = null;
                $user->save();
            }
        }
    }
}
