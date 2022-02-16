<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ArticlesIntegrationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCreateArticle(): void
    {
        $response = $this->postJson('/api/articles', [
            'article' => [
                'title' => 'Test article',
                'description' => 'Simple description',
                'body' => 'This is a short blogpost about testing',
                'tagList' => [
                    'test',
                    'ignore'
                ]
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJson(static function (AssertableJson $json) {
            $json->where('article.title', 'Test article')
                ->where('article.description', 'Simple description')
                ->where('article.body', 'This is a short blogpost about testing')
                ->where('article.tagList', ['test', 'ignore'])
                ->whereType('article.createdAt', 'string')
                ->whereType('article.updatedAt', 'string')
                ->where('article.slug', 'test-article')
                ->where('article.favorited', false)
                ->where('article.favoritesCount', 0)
                ->where('author.username', 'bob')
                ->where('author.bio', 'programming "cewebrity", missing my girl alice, morning person')
                ->where('author.image', '/bob.png')
                ->where('author.following', false);
        });
    }
}
