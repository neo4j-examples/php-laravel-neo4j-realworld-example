<?php

namespace Tests\Feature;

use Illuminate\Support\Collection;
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
            'username' => 'bob',
            'email' => 'bob.ross@gmail.com',
            'password' => '123456'
        ]);

        $response->assertStatus(201);
    }

    /**
     * @depends testCreateUser
     */
    public function testLogin(): void
    {
        $response = $this->postJson('/api/users/login', [
            'email' => 'bob.ross@gmail.com',
            'password' => '123456'
        ]);

        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('user.email', 'bob.ross@gmail.com')
                ->has('user.token')
                ->where('user.username', 'bob')
                ->where('user.bio', '')
                ->where('user.image', '');
        });

        self::$token = $response->json('user.token');
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
        });
    }

    /**
     * @depends testLogin
     */
    public function testPutUser(): void
    {
        $response = $this->putJson('/api/user', [
            'email' => 'bob.ross@gmail.com',
            'token' => self::$token,
            'username' => 'bob',
            'bio' => 'programming "cewebrity", missing my girl alice, morning person',
            'image' => '/bob.png'
        ], ['Authorization' => self::$token]);

        $response->assertStatus(200);

        $response->assertJson(static function (AssertableJson $json) {
            $json->where('user.email', 'bob.ross@gmail.com')
                ->has('user.token')
                ->where('user.username', 'Bob')
                ->where('user.bio', 'programming "cewebrity", missing my girl alice, morning person')
                ->where('user.image', '/bob.png');
        });
    }
}
