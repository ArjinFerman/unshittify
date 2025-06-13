<?php

namespace App\Support\Query;

use Closure;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EagerLoadJoinBuilder extends Builder
{
    protected array $withJoins = [];

    public function withJoin($name): self
    {
        $names = explode('.', $name);
        $model = null;
        /** @var Relation $relation */
        $relation = null;
        foreach ($names as $namePart) {
            if ($relation)
                $model = $relation->getRelated();
            else
                $model = $this->getModel();

            if (!method_exists($model->newInstance(), $namePart))
                return $this;

            $relation = $model->newInstance()->$namePart();

            if (!($relation instanceof BelongsTo))
                return $this;
        }

        $foreignTable = $relation->getRelated()->getTable();
        $ownerTable = $model->getTable();
        $foreignKey = $relation->getForeignKeyName();
        $ownerKey = $relation->getOwnerKeyName();
        $columns = [];
        if (count($names) <= 1)
            $columns[] = "$ownerTable.*";
        $columns[] = "$foreignTable.id AS $name.id";

        foreach ($relation->getRelated()->getFillable() as $column) {
            $columns[] = "$foreignTable.$column AS $name.$column";
        }

        $this->join($foreignTable, "$foreignTable.$ownerKey", '=', "$ownerTable.$foreignKey");

        if(empty($this->withJoins))
            $this->select($columns);
        else
            $this->addSelect($columns);

        $this->withJoins[] = $namePart;
        return $this;
    }
}
