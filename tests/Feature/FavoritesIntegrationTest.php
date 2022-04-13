<?php

namespace Tests\Feature;

use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;
use Laudis\Neo4j\Basic\Session;
use Tests\TestCase;

class FavoritesIntegrationTest extends TestCase
{
    private static ?string $token = null;

    /**
     * @doesNotPerformAssertions
     */
    public function testFillDatabase(): void
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

        $this->postJson('/api/articles', [
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

        $this->postJson('/api/articles', [
            'article' => [
                'title' => 'Test article 2',
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

    /**
     * @depends testFillDatabase
     */
    public function testFavorite(): void
    {
        $response = $this->postJson('/api/articles/test-article/favorite', [], ['Authorization' => self::$token]);

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
                ->where('article.favorited', true)
                ->where('article.favoritesCount', 1)
                ->where('article.author.username', 'bob')
                ->where('article.author.bio', 'programming "cewebrity", missing my girl alice, morning person')
                ->where('article.author.image', '/bob.png')
                ->where('article.author.following', false);
        });
    }

    /**
     * @depends testFillDatabase
     */
    public function testUnfavorite(): void
    {
        $response = $this->deleteJson('/api/articles/test-article/favorite', [], ['Authorization' => self::$token]);

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
}
