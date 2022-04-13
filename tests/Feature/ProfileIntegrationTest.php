<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Laudis\Neo4j\Basic\Session;
use Tests\TestCase;

class ProfileIntegrationTest extends TestCase
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

        $this->postJson('/api/users', [
            'user' => [
                'username' => 'alice',
                'email' => 'alice.ross@gmail.com',
                'password' => '123456'
            ]
        ]);
    }

    /**
     * @depends testCreateUser
     */
    public function testFollowProfile(): void
    {
        $response = $this->postJson('/api/profiles/alice/follow', [] , ['Authorization' => self::$token]);

        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('profile.username', 'alice')
                ->where('profile.bio', '')
                ->where('profile.image', '')
                ->where('profile.following', true);
        });
    }

    /**
     * @depends testFollowProfile
     */
    public function testUnfollowProfile(): void
    {
        $response = $this->deleteJson('/api/profiles/alice/follow', [] , ['Authorization' => self::$token]);

        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('profile.username', 'alice')
                ->where('profile.bio', '')
                ->where('profile.image', '')
                ->where('profile.following', false);
        });
    }
}
