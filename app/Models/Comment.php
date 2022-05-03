<?php

namespace App\Models;

use DateTimeInterface;
use const DATE_ATOM;

class Comment
{
    public function __construct(
        public readonly int $id,
        public readonly DateTimeInterface $createdAt,
        public readonly ?DateTimeInterface $updatedAt,
        public readonly string $body
    )
    {
    }

    public static function fromArray(array $array): self
    {
        return new self(
            id: $array['id'],
            createdAt: $array['createdAt']->toDateTime()->format(DATE_ATOM),
            updatedAt: $array['updatedAt']?->toDateTime()->format(DATE_ATOM),
            body: $array['body'],
        );
    }
}
