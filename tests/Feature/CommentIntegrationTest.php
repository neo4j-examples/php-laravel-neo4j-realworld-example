<?php

namespace Tests\Feature;

use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;
use Laudis\Neo4j\Basic\Session;
use Tests\TestCase;

class CommentIntegrationTest extends TestCase
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
    public function testComment(): void
    {
        $response = $this->postJson('/api/articles/test-article/comments', ['comment' => ['body' => 'Great blog post!']], ['Authorization' => self::$token]);

        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('comment.id', 0)
                ->whereType('comment.createdAt', 'string')
                ->whereType('comment.updatedAt', 'string')
                ->where('comment.body', 'Great blog post!')
                ->where('comment.author.username', 'bob')
                ->where('comment.author.bio', 'programming "cewebrity", missing my girl alice, morning person')
                ->where('comment.author.image', '/bob.png')
                ->where('comment.author.following', false);
        });
    }

    /**
     * @depends testComment
     */
    public function testUncomment(): void
    {
        $response = $this->deleteJson('/api/articles/test-article/comments/0', [], ['Authorization' => self::$token]);

        $response->assertStatus(200);
    }
}
