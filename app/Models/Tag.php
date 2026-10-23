<?php

namespace App\Models;

use App\Models\Relations\Relationship;

/**
 * @property string $name
 */
class Tag extends Node
{
    protected $fillable = ['name'];

    public function articles(): Relationship
    {
        return $this->matchRelationship(Article::class, 'TAGGED>');
    }
}
