<?php

namespace App\Models;

use App\Models\Relations\Relationship;
use App\Models\Relations\SingleRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Neo4j\Neo4jLaravel\Neo4jModel;

abstract class Node extends Neo4jModel
{
    public function singleRelationship(string $related, string $relationshipType): SingleRelationship
    {
        $instance = $this->newRelatedInstance($related);

        return new SingleRelationship(
            $instance->newQuery(),
            $this,
            $relationshipType,
            $this->getKeyName(),
            $instance->getKeyName()
        );
    }

    protected function newMatchRelationship(
        Builder $query,
        Model $parent,
        string $relationshipType,
        string $localKey,
        string $relatedKey,
    ): Relationship {
        return new Relationship($query, $parent, $relationshipType, $localKey, $relatedKey);
    }
}
