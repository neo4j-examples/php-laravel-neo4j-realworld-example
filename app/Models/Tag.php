<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Vinelab\NeoEloquent\Eloquent\Model;

/**
 * @property string $name
 */
class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function articles(): BelongsToMany
    {
        return $this->belongsToManyRelation(Article::class, 'TAGGED>')
            ->withTimestamps();
    }
}
