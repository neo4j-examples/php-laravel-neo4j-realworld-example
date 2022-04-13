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
}
