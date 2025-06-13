<?php

namespace App\Support\Query;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

trait EagerLoadJoinTrait
{

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new EagerLoadJoinBuilder($query);
    }

    public function newFromBuilder($attributes = [], $connection = null)
    {
        /** @var Model $model */
        $model = parent::newFromBuilder($attributes, $connection);
        return $this->setupJoinRelations($model, $attributes);
    }

    protected function setupJoinRelations(Model $model, $attributes = [])
    {
        $processedRelations = [];
        foreach ($model->attributesToArray() as $attribute => $value) {
            $relationName = explode('.', $attribute);
            if (count($relationName) <= 1 || in_array($relationName[1], $processedRelations))
                continue;

            $attribute = array_pop($relationName);
            $relationName = implode('.', $relationName);
            if(!isset($processedRelations[$relationName]))
                $processedRelations[$relationName] = [];

            $processedRelations[$relationName][$attribute] = $value;
        }

        foreach ($processedRelations as $relationName => $relationAttributes) {
            /** @var Relation $relation */
            $relation = null;
            $parentModel = $model;
            $relationNameParts = explode('.', $relationName);
            foreach ($relationNameParts as $relationName) {
                if ($parentModel->relationLoaded($relationName))
                    $parentModel = $parentModel->getRelation($relationName);
                else
                    $relation = $parentModel->$relationName();
            }
            $relModel = $relation->getModel()->newFromBuilder($relationAttributes);
            $parentModel->setRelation($relationName, $relModel);
        }

        return $model;
    }

    protected function getWithJoinRelations(Model $model): array
    {
        $relations = [];
        foreach ($model->attributesToArray() as $attribute => $value) {
            $relation = explode('.', $attribute);
            if (count($relation) > 1 && !in_array($relation[1], $relations)) {
                $relations[] = $relation[1];
            }
        }
        return $relations;
    }
}
