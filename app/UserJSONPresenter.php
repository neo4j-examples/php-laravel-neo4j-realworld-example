<?php

namespace App;

use App\Models\User;
use Firebase\JWT\JWT;
use function env;
use function time;

class UserJSONPresenter
{
    public function __construct(private readonly UserRepository $repository)
    {
    }

    public function presentAsProfile(User $user): array
    {
        return [
            'username' => $user->username,
            'bio' => $user->bio,
            'image' => $user->image,
            'following' => $this->repository->following(
                auth()->user()?->getAuthIdentifier() ?? '',
                $user->username
            )
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
