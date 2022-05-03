<?php

namespace App\Models;

use DateTimeInterface;

class Article
{
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $description,
        public readonly string $body,
        public readonly DateTimeInterface $createdAt,
        public readonly DateTimeInterface $updatedAt
    )
    {
    }
}
