<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property UserModel $author
 * @property Collection $favoritedBy
 * @property int $favoriteCount
 * @property bool $favorited
 */
class ArticleModel extends Model
{
    use HasFactory;
    use HasSlug;

    protected $fillable = ['title', 'description', 'body'];

    protected $table = 'Article';

    protected $primaryKey = 'slug';

    public function comments(): HasMany
    {
        return $this->hasManyRelationship(CommentModel::class, 'HAS_COMMENT');
    }

    public function tags(): HasMany
    {
        return $this->hasManyRelationship(TagModel::class, 'TAGGED');
    }

    public function author(): BelongsTo
    {
        return $this->belongsToRelation(UserModel::class, 'AUTHORED');
    }

    public function favoritedBy(): HasMany
    {
        return $this->hasManyRelationship(UserModel::class, 'FAVORITED_BY');
    }

    public function getFavoritedAttribute(): bool
    {
        return $this->favoritedBy()
            ->where('User.email', auth()->id())
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
