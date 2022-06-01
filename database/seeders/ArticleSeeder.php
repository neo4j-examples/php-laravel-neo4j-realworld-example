<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;
use function rand;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::query()->chunk(200, function (User $user) {
            Article::factory()
                ->count(random_int(0, 5))
                ->for($user)
                ->create();
        });
    }
}
