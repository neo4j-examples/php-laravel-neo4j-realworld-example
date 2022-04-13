<?php

namespace App\Models;

use DateTimeInterface;

class Article
{
    /**
     * @param Tag[] $tags
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $description,
        public readonly string $body,
        public readonly array $tags,
        public readonly DateTimeInterface $createdAt,
        public readonly DateTimeInterface $updatedAt,
        public readonly bool $favorited,
        public readonly bool $favoritesCount,
        public readonly UserModel $author
    )
    {
    }
}
