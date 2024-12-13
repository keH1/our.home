<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'phone' => '89999999999',
            'email' => 'tsarev@gmail.com',
            'password' => Hash::make('qwerty1qwerty'),
        ]);
        $user->assignRole(Roles::ADMIN);
    }
}
