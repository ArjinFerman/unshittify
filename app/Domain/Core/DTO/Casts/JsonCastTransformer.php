<?php

namespace App\Domain\Core\DTO\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class JsonCastTransformer implements Cast, Transformer
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): string
    {
        return json_decode($value);
    }

    public function transform(DataProperty $property, mixed $value, TransformationContext $context): string
    {
        return json_encode($value);
    }
}
