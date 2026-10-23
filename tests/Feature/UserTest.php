<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Laudis\Neo4j\Contracts\SessionInterface;
use PHPUnit\Framework\Attributes\Depends;
use Tests\TestCase;

final class UserTest extends TestCase
{
    private static ?string $token = null;

    public function testCreateUser(): void
    {
        $this->app->get(SessionInterface::class)->run('MATCH (x) DETACH DELETE x');

        $response = $this->postJson('/api/users', [
            'user' => [
                'username' => 'bob',
                'email' => 'bob.ross@gmail.com',
                'password' => '1erz23456aB@'
            ]
        ]);

        $response->assertStatus(201);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('user.email', 'bob.ross@gmail.com')
                ->has('user.token')
                ->where('user.username', 'bob')
                ->where('user.bio', null)
                ->where('user.image', null);
        });
    }

    #[Depends('testCreateUser')]
    public function testLogin(): void
    {
        $response = $this->postJson('/api/users/login', [
            'user' => [
                'email' => 'bob.ross@gmail.com',
                'password' => '1erz23456aB@'
            ],
        ]);

        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('user.email', 'bob.ross@gmail.com')
                ->has('user.token')
                ->where('user.username', 'bob')
                ->where('user.bio', null)
                ->where('user.image', null);
        });

        self::$token = 'Bearer ' . $response->json('user.token');
    }

    #[Depends('testCreateUser')]
    public function testLoginInvalid(): void
    {
        $response = $this->postJson('/api/users/login', [
            'user' => [
                'email' => 'abc'
            ],
        ]);

        $response->assertStatus(422);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('errors.password', ["can't be blank"]);
        });
    }

    #[Depends('testLogin')]
    public function testGetUser(): void
    {
        $response = $this->getJson('/api/user', ['Authorization' => self::$token]);

        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('user.email', 'bob.ross@gmail.com')
                ->has('user.token')
                ->where('user.username', 'bob')
                ->where('user.bio', null)
                ->where('user.image', null);

            self::assertNotEquals($json->toArray()['user']['token'], self::$token);
        });
    }

    #[Depends('testLogin')]
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
