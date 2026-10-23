<?php

namespace App\Models;

use App\Models\Relations\Relationship;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $name
 */
class Tag extends Node
{
    use HasFactory;

    protected $fillable = ['name'];

    public function articles(): Relationship
    {
        return $this->matchRelationship(Article::class, 'TAGGED>');
    }
}
