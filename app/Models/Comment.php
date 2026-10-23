<?php

namespace App\Models;

use App\Models\Relations\SingleRelationship;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use const DATE_ATOM;

/**
 * @property string $id
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 * @property string $body
 *
 * @property User $author
 * @property Article $article@
 */
class Comment extends Node
{
    use HasFactory;

    public const CREATED_AT = 'createdAt';

    public const UPDATED_AT = 'updatedAt';

    protected $dateFormat = DATE_ATOM;

    protected $fillable = ['body'];

    public function author(): SingleRelationship
    {
        return $this->singleRelationship(User::class, '<AUTHORED');
    }

    public function article(): SingleRelationship
    {
        return $this->singleRelationship(Article::class, '<HAS_COMMENT');
    }
}
