<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run()
    {
        Branch::create([
            'name' => 'Main Branch',
            'address' => '123 Church St.',
        ]);
    }
}

