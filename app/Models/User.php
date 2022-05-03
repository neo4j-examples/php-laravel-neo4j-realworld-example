<?php

namespace App\Models;

class User
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $bio,
        public readonly string $image,
        public readonly string $passwordHash,
    )
    {
    }

    public static function fromArray(array $array): self
    {
        return new self(
            username: $array['username'],
            email: $array['email'],
            bio: $array['bio'],
            image: $array['image'],
            passwordHash: $array['passwordHash'],
        );
    }
}
