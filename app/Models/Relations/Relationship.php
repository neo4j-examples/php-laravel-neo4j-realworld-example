<?php

namespace App\Models\Relations;

use Neo4j\Neo4jLaravel\Relations\MatchRelationship;

class Relationship extends MatchRelationship
{
    /**
     * Uses MERGE so attaching an already related node does not create a duplicate relationship.
     */
    public function attach($ids, array $attributes = []): void
    {
        $this->parentQuery('attach')->upsertRelationships(
            $this->relationshipType,
            $this->relatedLabel(),
            $this->relationshipRows('attach', $ids, $attributes),
        );
    }
}
