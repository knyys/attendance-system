<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name'      => 'admin',
            'email'     => 'admin@email.com',
            'password'  => Hash::make('admin1111'),
            'is_admin' => 1,
        ]);

        DB::table('users')->insert([
            'name'      => 'user',
            'email'     => 'user@email.com',
            'password'  => Hash::make('user1111'),
            'is_admin' => 0,
        ]);
    }
}
