<?php

namespace Tests\Feature;

use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;
use Laudis\Neo4j\Basic\Session;
use Tests\TestCase;

class ArticlesIntegrationTest extends TestCase
{
    public function testCreateArticle(): void
    {
        $this->app->get(Session::class)->run('MATCH (x) DETACH DELETE x');

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
                ->where('article.tagList', function (Collection $x) {
                    self::assertEqualsCanonicalizing(['ignore', 'test'], $x->toArray());

                    return true;
                })
                ->whereType('article.createdAt', 'string')
                ->whereType('article.updatedAt', 'string')
                ->where('article.slug', 'test-article')
                ->where('article.favorited', false)
                ->where('article.favoritesCount', 0)
                ->where('article.author.username', 'bob')
                ->where('article.author.bio', 'programming "cewebrity", missing my girl alice, morning person')
                ->where('article.author.image', '/bob.png')
                ->where('article.author.following', false);
        });
    }

    /**
     * @depends testCreateArticle
     */
    public function testGetArticle(): void
    {
        $response = $this->get('/api/articles/test-article');

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) {
            $json->where('article.title', 'Test article')
                ->where('article.description', 'Simple description')
                ->where('article.body', 'This is a short blogpost about testing')
                ->where('article.tagList', function (Collection $x) {
                    self::assertEqualsCanonicalizing(['ignore', 'test'], $x->toArray());

                    return true;
                })
                ->whereType('article.createdAt', 'string')
                ->whereType('article.updatedAt', 'string')
                ->where('article.slug', 'test-article')
                ->where('article.favorited', false)
                ->where('article.favoritesCount', 0)
                ->where('article.author.username', 'bob')
                ->where('article.author.bio', 'programming "cewebrity", missing my girl alice, morning person')
                ->where('article.author.image', '/bob.png')
                ->where('article.author.following', false);
        });
    }

    /**
     * @depends testCreateArticle
     */
    public function testGetArticles(): void
    {
        $response = $this->get('/api/articles');

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) {
            $json->where('articles.0.title', 'Test article')
                ->where('articles.0.description', 'Simple description')
                ->where('articles.0.body', 'This is a short blogpost about testing')
                ->where('articles.0.tagList', function (Collection $x) {
                    self::assertEqualsCanonicalizing(['ignore', 'test'], $x->toArray());

                    return true;
                })
                ->whereType('articles.0.createdAt', 'string')
                ->whereType('articles.0.updatedAt', 'string')
                ->where('articles.0.slug', 'test-article')
                ->where('articles.0.favorited', false)
                ->where('articles.0.favoritesCount', 0)
                ->where('articles.0.author.username', 'bob')
                ->where('articles.0.author.bio', 'programming "cewebrity", missing my girl alice, morning person')
                ->where('articles.0.author.image', '/bob.png')
                ->where('articles.0.author.following', false)
                ->where('articlesCount', 1);
        });
    }

    /**
     * @depends testCreateArticle
     */
    public function testPutArticle(): void
    {
        $response = $this->putJson('/api/articles/test-article', [
            'article' => [
                'body' => 'This is a short blogpost about testing and developing. EDIT: extended about section.'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) {
            $json->where('article.title', 'Test article')
                ->where('article.description', 'Simple description')
                ->where('article.body', 'This is a short blogpost about testing and developing. EDIT: extended about section.')
                ->where('article.tagList', function (Collection $x) {
                    self::assertEqualsCanonicalizing(['ignore', 'test'], $x->toArray());

                    return true;
                })
                ->whereType('article.createdAt', 'string')
                ->whereType('article.updatedAt', 'string')
                ->where('article.slug', 'test-article')
                ->where('article.favorited', false)
                ->where('article.favoritesCount', 0)
                ->where('article.author.username', 'bob')
                ->where('article.author.bio', 'programming "cewebrity", missing my girl alice, morning person')
                ->where('article.author.image', '/bob.png')
                ->where('article.author.following', false);
        });
    }

    /**
     * @depends testPutArticle
     */
    public function testPutAgain(): void
    {
        $this->testPutArticle();
    }

    /**
     * @depends testCreateArticle
     */
    public function testDelete(): void
    {
        $response = $this->delete('/api/articles/test-article');

        $response->assertStatus(200);
    }

    /**
     * @depends testDelete
     */
    public function testGetDeleted(): void
    {
        $response = $this->get('/api/articles/test-article');

        $response->assertStatus(404);
    }
}
