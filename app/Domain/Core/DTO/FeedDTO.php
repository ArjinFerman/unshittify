<?php

namespace App\Domain\Core\DTO;

use App\Domain\Core\DTO\Casts\CompositeIdCastTransformer;
use App\Domain\Core\DTO\Casts\JsonCastTransformer;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Traits\DTO\HasMetadata;
use App\Support\CompositeId;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;

class FeedDTO extends BaseDTO
{
    use HasMetadata;

    public function __construct(
        #[WithCastAndTransformer(CompositeIdCastTransformer::class)]
        public CompositeId $composite_id,
        public ?AuthorDTO  $author,
        public string      $name,
        public FeedStatus  $status,
        public string      $url,
        #[WithCastAndTransformer(JsonCastTransformer::class)]
        public array       $metadata = [],
    )
    {
    }

    public function getDisplayName(): string
    {
        return $this->metadata['display_name'] ?? $this->name;
    }
}
