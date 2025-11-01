<?php

namespace App\Domain\Core\DTO;

use App\Domain\Core\DTO\Casts\JsonCastTransformer;
use App\Domain\Core\Traits\DTO\HasMetadata;
use App\Support\CompositeId;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;

/**
 * @property Collection<int, EntryReferenceDTO> $references
 * @property Collection<int, MediaDTO> $media
 * @property Collection<int, TagDTO> $tags
 */
class EntryDTO extends BaseDTO
{
    use HasMetadata;

    public function __construct(
        public CompositeId $composite_id,
        public CompositeId $feed_composite_id,
        public string      $url,
        public string      $title,
        public string      $content,
        public Carbon      $published_at,
        #[WithCastAndTransformer(JsonCastTransformer::class)]
        public array       $metadata = [],
        public ?FeedDTO    $feed = null,
        public ?Collection $references = null,
        public ?Collection $media = null,
        public ?Collection $tags = null,
    )
    {
    }
}
