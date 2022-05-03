<?php

namespace Tests\Feature;

use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Laudis\Neo4j\Basic\Session;
use Tests\TestCase;

class ArticlesIntegrationTest extends TestCase
{
    private static ?string $token = null;

    /**
     * @doesNotPerformAssertions
     */
    public function testCreateUser(): void
    {
        $this->app->get(Session::class)->run('MATCH (x) DETACH DELETE x');

        $this->postJson('/api/users', [
            'user' => [
                'username' => 'bob',
                'email' => 'bob.ross@gmail.com',
                'password' => '123456'
            ]
        ]);

        $response = $this->postJson('/api/users/login', [
            'user' => [
                'email' => 'bob.ross@gmail.com',
                'password' => '123456'
            ],
        ]);

        self::$token = 'Bearer ' . $response->json('user.token');

        $this->putJson('/api/user', [
            'user' => [
                'email' => 'bob.ross@gmail.com',
                'token' => self::$token,
                'username' => 'bob',
                'bio' => 'programming "cewebrity", missing my girl alice, morning person',
                'image' => '/bob.png'
            ]
        ], ['Authorization' => self::$token]);
    }

    /**
     * @depends testCreateUser
     */
    public function testCreateArticle(): void
    {
        $response = $this->createTestArticle();

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
        ], ['Authorization' => self::$token]);

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
        $response = $this->delete('/api/articles/test-article', [], ['Authorization' => self::$token]);

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

    /**
     * @depends testGetDeleted
     */
    public function testMultipleCreations(): void
    {
        $response1 = $this->createTestArticle();
        $response2 = $this->createTestArticle();
        $response3 = $this->createTestArticle();
        $response4 = $this->createTestArticle();
        $response5 = $this->createTestArticle();

        self::assertEquals('test-article', $response1->json('article.slug'));
        self::assertEquals('test-article-1', $response2->json('article.slug'));
        self::assertEquals('test-article-2', $response3->json('article.slug'));
        self::assertEquals('test-article-3', $response4->json('article.slug'));
        self::assertEquals('test-article-4', $response5->json('article.slug'));
    }

    /**
     * @depends testGetDeleted
     */
    public function testCreateOtherArticle(): void
    {
        $response = $this->postJson('/api/articles', [
            'article' => [
                'title' => 'New article',
                'description' => 'Other description',
                'body' => 'Test if slug gets generated correctly',
                'tagList' => [
                    'test',
                ]
            ]
        ], [
            'Authorization' => self::$token
        ]);

        self::assertEquals('new-article', $response->json('article.slug'));
    }

    private function createTestArticle(): TestResponse
    {
        return $this->postJson('/api/articles', [
            'article' => [
                'title' => 'Test article',
                'description' => 'Simple description',
                'body' => 'This is a short blogpost about testing',
                'tagList' => [
                    'test',
                    'ignore'
                ]
            ]
        ], [
            'Authorization' => self::$token
        ]);
    }
}
