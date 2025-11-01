<?php

namespace App\Domain\Core\DTO;

use App\Domain\Core\DTO\Casts\CompositeIdCastTransformer;
use App\Domain\Core\DTO\Casts\JsonCastTransformer;
use App\Domain\Core\Enums\ReferenceType;
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
        #[WithCastAndTransformer(CompositeIdCastTransformer::class)]
        public CompositeId $composite_id,
        #[WithCastAndTransformer(CompositeIdCastTransformer::class)]
        public CompositeId $feed_composite_id,
        public string      $url,
        public string      $title,
        public string      $content,
        public Carbon      $published_at,
        public bool        $is_read,
        #[WithCastAndTransformer(JsonCastTransformer::class)]
        public array       $metadata = [],
        public ?FeedDTO    $feed = null,
        public ?Collection $references = null,
        public ?Collection $media = null,
        public ?Collection $tags = null,
    )
    {
    }

    public function isRepost(): bool
    {
        return $this->references?->where('ref_type', ReferenceType::REPOST)?->isEmpty() ?? false;
    }

    public function repost(): ?self
    {
        return $this->references?->where('ref_type', ReferenceType::REPOST)?->first()?->referenced_entry;
    }

    public function hasQuote(): bool
    {
        return $this->references?->where('ref_type', ReferenceType::QUOTE)?->isEmpty() ?? false;
    }

    public function quote(): ?self
    {
        return $this->references?->where('ref_type', ReferenceType::QUOTE)?->first()?->referenced_entry;
    }

    public function isStarred(): bool
    {
        return false;
    }
}
