<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Relations\HasMany;

/**
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $body
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 *
 * @property Collection $comments
 * @property Collection $tags
 * @property User $author
 * @property Collection $favoritedBy
 * @property int $favoriteCount
 * @property bool $favorited
 */
class Article extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = ['title', 'description', 'body'];

    protected $primaryKey = 'slug';

    public function comments(): HasMany
    {
        return $this->hasManyRelationship(Comment::class, 'HAS_COMMENT');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToManyRelation(Tag::class, '<TAGGED');
    }

    public function author(): BelongsTo
    {
        return $this->belongsToRelation(User::class, 'AUTHORED');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToManyRelation(User::class, '<FAVORITED');
    }

    public function getFavoritedAttribute(): bool
    {
        return $this->favoritedBy()
            ->where('User.username', auth()->id())
            ->exists();
    }

    public function getFavoriteCountAttribute(): bool
    {
        return $this->favoritedBy()->count();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }
}
