<?php

namespace App\Support;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class CompositeIdCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if (empty($value)) {
            return null;
        }

        return CompositeId::fromString($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        /** @var CompositeId $value */
        if ($value instanceof CompositeId) {
            return (string)$value;
        }

        return $value;
    }
}
