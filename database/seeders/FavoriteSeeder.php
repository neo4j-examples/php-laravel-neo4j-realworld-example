<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;
use function random_int;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Article::query()->chunk(200, function (Article $article) {
            $favoriters = User::query()
                ->inRandomOrder()
                ->take(random_int(0, 5));

            /** @var User $favoriter */
            foreach ($favoriters as $favoriter) {
                $favoriter->favorited()->attach($article);
            }
        });
    }
}
