<?php

namespace App\Domain\Core\DTO;

use App\Domain\Core\Enums\MediaType;
use App\Domain\Core\Enums\ReferenceType;
use App\Support\CompositeId;

class EntryReferenceDTO extends BaseDTO
{
    public function __construct(
        public CompositeId   $entry_composite_id,
        public CompositeId   $ref_entry_composite_id,
        public ReferenceType $ref_type,
        public ?EntryDTO     $referenced_entry,
    )
    {
    }
}
