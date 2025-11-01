<?php

namespace App\Domain\Core\DTO;

use App\Domain\Core\DTO\Casts\CompositeIdCastTransformer;
use App\Support\CompositeId;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;

class MediableDTO extends BaseDTO
{
    public function __construct(
        #[WithCastAndTransformer(CompositeIdCastTransformer::class)]
        public CompositeId $media_composite_id,
        #[WithCastAndTransformer(CompositeIdCastTransformer::class)]
        public CompositeId $mediable_composite_id,
        public string $mediable_type,
    )
    {
    }
}
