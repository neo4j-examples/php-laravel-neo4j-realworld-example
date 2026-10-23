<?php

namespace App\Models\Relations;

use Illuminate\Database\Eloquent\Collection;

class SingleRelationship extends Relationship
{
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }

    public function match(array $models, Collection $results, $relation)
    {
        foreach (parent::match($models, $results, $relation) as $model) {
            $related = $model->getRelation($relation);

            if ($related instanceof Collection) {
                $model->setRelation($relation, $related->first());
            }
        }

        return $models;
    }

    public function getResults()
    {
        if ($this->parent->getAttribute($this->localKey) === null) {
            return null;
        }

        return $this->query->first();
    }
}
