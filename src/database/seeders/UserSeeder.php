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
        /********** ユーザー **********/
        DB::table('users')->insert([
            'name'      => 'user',
            'email'     => 'user@email.com',
            'password'  => Hash::make('user1111'),
            'is_admin' => 0,
        ]);

        DB::table('users')->insert([
            'name'      => 'user2',
            'email'     => 'user2@email.com',
            'password'  => Hash::make('user2222'),
            'is_admin' => 0,
        ]);

        DB::table('users')->insert([
            'name'      => 'user3',
            'email'     => 'user3@email.com',
            'password'  => Hash::make('user3333'),
            'is_admin' => 0,
        ]);


        /********** 管理者ユーザー **********/
        DB::table('users')->insert([
            'name'      => 'admin',
            'email'     => 'admin@email.com',
            'password'  => Hash::make('admin1111'),
            'is_admin' => 1,
        ]);
    }
}
