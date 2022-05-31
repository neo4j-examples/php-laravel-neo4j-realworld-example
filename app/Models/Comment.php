<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vinelab\NeoEloquent\Eloquent\Model;
use const DATE_ATOM;

/**
 * @property int $id
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 * @property string $body
 *
 * @property User $author
 */
class Comment extends Model
{
    use HasFactory;

    protected $dateFormat = DATE_ATOM;

    protected $fillable = ['body'];

    public function author(): BelongsTo
    {
        return $this->belongsToRelation(User::class, 'AUTHORED');
    }

    public function article(): BelongsTo
    {
        return $this->belongsToRelation(Article::class, 'HAS_COMMENT');
    }
}
