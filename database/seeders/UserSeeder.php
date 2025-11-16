<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'branch_id' => 1,
            'role' => 'Admin',
            'username' => 'carlstephen',
            'email' => 'admin1@example.com',
            'password' => bcrypt('carlpogi123'),
            'first_name' => 'Carl Stephen',
            'last_name' => 'Vergara',
        ]);
    }
}
