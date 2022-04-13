<?php

namespace App\Models;

use DateTimeInterface;

class Comment
{
    public function __construct(
        public readonly int $id,
        public readonly DateTimeInterface $createdAt,
        public readonly DateTimeInterface $updatedAt,
        public readonly UserModel $author
    )
    {
    }
}
