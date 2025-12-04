<?php

namespace App\Domain\Core\DTO;

use App\Domain\Core\DTO\Casts\CompositeIdCastTransformer;
use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry;
use App\Support\CompositeId;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;

class EntryReferenceDTO extends BaseDTO
{
    public function __construct(
        #[WithCastAndTransformer(CompositeIdCastTransformer::class)]
        public CompositeId   $entry_composite_id,
        #[WithCastAndTransformer(CompositeIdCastTransformer::class)]
        public CompositeId   $ref_entry_composite_id,
        public ReferenceType $ref_type,
        public ?EntryDTO     $referenced_entry = null,
    )
    {
    }

    public static function fromModel(Entry $entry): self
    {
        $pivot = $entry->relationLoaded('pivot') ?
            $entry->pivot->toArray() : [
                'entry_composite_id' => $entry->pivot_entry_composite_id,
                'ref_entry_composite_id' => $entry->pivot_ref_entry_composite_id,
                'ref_type' => $entry->pivot_ref_type,
            ];

        $pivot['referenced_entry'] = $entry;

        return self::from($pivot);
    }
}
