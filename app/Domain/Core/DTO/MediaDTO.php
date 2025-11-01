<?php

namespace App\Domain\Core\DTO;

use App\Domain\Core\DTO\Casts\JsonCastTransformer;
use App\Domain\Core\Enums\MediaType;
use App\Support\CompositeId;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;

class MediaDTO extends BaseDTO
{
    public function __construct(
        public CompositeId $composite_id,
        public MediaType   $type,
        public string      $url,
        public string      $content_type,
        #[WithCastAndTransformer(JsonCastTransformer::class)]
        public array       $metadata = [],
    )
    {
    }
}
