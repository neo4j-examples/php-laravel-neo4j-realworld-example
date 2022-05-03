<?php

namespace App\Presenters;

use App\Models\User;
use App\Repositories\UserRepository;
use Firebase\JWT\JWT;
use JetBrains\PhpStorm\ArrayShape;
use function env;
use function time;

class UserJSONPresenter
{
    public function __construct()
    {
    }

    public function presentAsProfile(User $user, bool $following): array
    {
        return [
            'username' => $user->username,
            'bio' => $user->bio,
            'image' => $user->image,
            'following' => $following
        ];
    }

    public function presentAsUser(User $user): array
    {
        $jwt = $this->createToken($user);

        return [
            'email' => $user->email,
            'username' => $user->username,
            'bio' => $user->bio,
            'image' => $user->image,
            'token' => $jwt
        ];
    }

    private function createToken(User $user): string
    {
        $key = env('APP_KEY');
        $payload = array(
            "iss" => env('APP_URL'),
            "aud" => env('APP_URL'),
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + (24 * 60 * 60),
            "user" => [
                'email' => $user->email,
                'username' => $user->username,
                'bio' => $user->bio,
                'image' => $user->image
            ]
        );

        return JWT::encode($payload, $key, 'HS256');
    }
}
