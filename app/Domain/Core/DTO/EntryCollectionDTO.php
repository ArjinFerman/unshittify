<?php

namespace App\Domain\Core\DTO;


use Illuminate\Support\Collection;;

class EntryCollectionDTO extends BaseDTO
{
    public function __construct(
        /** @var Collection<int, EntryDTO> $items */
        public Collection $items,
        public ?string $top_cursor = null,
        public ?string $bottom_cursor = null,
    )
    {
    }
}
