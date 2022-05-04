<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Laudis\Neo4j\Basic\Session;
use Tests\TestCase;

final class UserTest extends TestCase
{
    private static ?string $token = null;

    public function testCreateUser(): void
    {
        $this->app->get(Session::class)->run('MATCH (x) DETACH DELETE x');

        $response = $this->postJson('/api/users', [
            'user' => [
                'username' => 'bob',
                'email' => 'bob.ross@gmail.com',
                'password' => '123456'
            ]
        ]);

        $response->assertStatus(201);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('user.email', 'bob.ross@gmail.com')
                ->has('user.token')
                ->where('user.username', 'bob')
                ->where('user.bio', '')
                ->where('user.image', '');
        });
    }

    /**
     * @depends testCreateUser
     */
    public function testLogin(): void
    {
        $response = $this->postJson('/api/users/login', [
            'user' => [
                'email' => 'bob.ross@gmail.com',
                'password' => '123456'
            ],
        ]);

        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('user.email', 'bob.ross@gmail.com')
                ->has('user.token')
                ->where('user.username', 'bob')
                ->where('user.bio', '')
                ->where('user.image', '');
        });

        self::$token = 'Bearer ' . $response->json('user.token');
    }

    /**
     * @depends testCreateUser
     */
    public function testLoginInvalid(): void
    {
        $response = $this->postJson('/api/users/login', [
            'user' => [
                'email' => 'abc'
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('errors.body', 'The user.email must be a valid email address.
The user.password field is required.');
        });

        self::$token = 'Bearer ' . $response->json('user.token');
    }

    /**
     * @depends testLogin
     */
    public function testGetUser(): void
    {
        $response = $this->getJson('/api/user', ['Authorization' => self::$token]);

        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('user.email', 'bob.ross@gmail.com')
                ->has('user.token')
                ->where('user.username', 'bob')
                ->where('user.bio', '')
                ->where('user.image', '');

            self::assertNotEquals($json->toArray()['user']['token'], self::$token);
        });
    }

    /**
     * @depends testLogin
     */
    public function testPutUser(): void
    {
        $response = $this->putJson('/api/user', [
            'user' => [
                'email' => 'bob.ross@gmail.com',
                'token' => self::$token,
                'username' => 'bob',
                'bio' => 'programming "cewebrity", missing my girl alice, morning person',
                'image' => '/bob.png'
            ]
        ], ['Authorization' => self::$token]);

        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('user.email', 'bob.ross@gmail.com')
                ->has('user.token')
                ->where('user.username', 'bob')
                ->where('user.bio', 'programming "cewebrity", missing my girl alice, morning person')
                ->where('user.image', '/bob.png');
        });
    }
}
