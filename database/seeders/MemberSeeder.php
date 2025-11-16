<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class MemberSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'branch_id' => 1,
            'role' => 'Member',
            'username' => 'member01',
            'email' => 'member01@example.com',
            'password' => bcrypt('password123'),
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
        ]);
    }
}

