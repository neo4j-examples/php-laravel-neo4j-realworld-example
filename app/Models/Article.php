<?php

namespace App\Models;

use App\Models\Relations\Relationship;
use App\Models\Relations\SingleRelationship;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property string $id
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
class Article extends Node
{
    use HasSlug;

    public const CREATED_AT = 'createdAt';

    public const UPDATED_AT = 'updatedAt';

    protected $fillable = ['title', 'description', 'body'];

    protected $dateFormat = 'Y-m-d\TH:i:s.vP';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function comments(): Relationship
    {
        return $this->matchRelationship(Comment::class, 'HAS_COMMENT>');
    }

    public function tags(): Relationship
    {
        return $this->matchRelationship(Tag::class, '<TAGGED');
    }

    public function author(): SingleRelationship
    {
        return $this->singleRelationship(User::class, '<AUTHORED');
    }

    public function favoritedBy(): Relationship
    {
        return $this->matchRelationship(User::class, '<FAVORITED');
    }

    public function favoritedBySelf(): SingleRelationship
    {
        // define it as a relationship so it can be eagerloaded
        return $this->singleRelationship(User::class, '<FAVORITED')
            ->where('username', auth()->id());
    }

    public function getFavoritedAttribute(): bool
    {
        return $this->favoritedBySelf !== null;
    }

    public function getFavoriteCountAttribute(): int
    {
        return $this->favoritedBy()->count();
    }

    public function getSlugOptions(): SlugOptions
    {
        // A suffix generator makes the package check uniqueness with equality queries instead of LIKE,
        // which the Neo4j query grammar does not support.
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->usingSuffixGenerator(static fn (string $slug, int $iteration): string => (string) ($iteration + 1));
    }
}
