<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use function random_int;

class FollowerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::query()->chunk(200, function (User $user) {
            $followers = User::query()
                ->inRandomOrder()
                ->take(random_int(0, 5));

            /** @var User $follower */
            foreach ($followers as $follower) {
                $follower->following()->attach($user);
            }
        });
    }
}
