<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use function mt_rand;
use function random_int;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::factory()
            ->count(20)
            ->create();
    }
}
