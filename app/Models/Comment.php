<?php

namespace App\Models;

use App\Models\Relations\SingleRelationship;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function ctype_digit;
use function is_int;

/**
 * @property int $id
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 * @property string $body
 *
 * @property User $author
 * @property Article $article@
 */
class Comment extends Node
{
    public const CREATED_AT = 'createdAt';

    public const UPDATED_AT = 'updatedAt';

    protected $dateFormat = 'Y-m-d\TH:i:s.vP';

    protected $fillable = ['body'];

    protected static function booted(): void
    {
        // The RealWorld spec requires integer comment ids, so they come from a sequence node instead of a UUID.
        static::creating(static function (Comment $comment): void {
            $comment->setAttribute($comment->getKeyName(), (int) DB::connection('neo4j')->selectOne(
                "MERGE (s:Sequence {name: 'Comment'}) ON CREATE SET s.value = 0 SET s.value = s.value + 1 RETURN s.value AS value"
            )->value);
        });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (!is_int($value) && !ctype_digit((string) $value)) {
            return null;
        }

        return parent::resolveRouteBinding((int) $value, $field);
    }

    public function author(): SingleRelationship
    {
        return $this->singleRelationship(User::class, '<AUTHORED');
    }

    public function article(): SingleRelationship
    {
        return $this->singleRelationship(Article::class, '<HAS_COMMENT');
    }
}
