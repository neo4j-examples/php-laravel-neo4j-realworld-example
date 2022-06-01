<?php

namespace App\Http\Resources;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use function time;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $jwt = $this->createToken();

        return [
            'email' => $this->email,
            'username' => $this->username,
            'bio' => $this->bio,
            'image' => $this->image,
            'token' => $jwt
        ];
    }

    private function createToken(): string
    {
        $key = Config::get('app.key');
        $payload = array(
            "iss" => Config::get('app.url'),
            "aud" => Config::get('app.url'),
            "iat" => time(),
            "nbf" => time(),
            "exp" => time() + (24 * 60 * 60),
            "user" => [
                'email' => $this->email,
                'username' => $this->username,
                'bio' => $this->bio,
                'image' => $this->image
            ]
        );

        return JWT::encode($payload, $key, 'HS256');
    }
}
