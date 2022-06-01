<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use function random_int;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $tags = Tag::factory()
            ->count(20)
            ->create();

        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $articles = Article::query()
                ->inRandomOrder()
                ->take(random_int(0, 5));

            foreach ($articles as $article) {
                $tag->articles()->attach($article);
            }
        }
    }
}
