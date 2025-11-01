<?php

namespace App\Domain\Core\DTO\Casts;

use App\Support\CompositeId;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class CompositeIdCastTransformer implements Cast, Transformer
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return CompositeId::fromString($value);
    }

    public function transform(DataProperty $property, mixed $value, TransformationContext $context): string
    {
        return (string)$value;
    }
}
