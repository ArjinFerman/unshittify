<?php

namespace App\Domain\Core\DTO;


use Illuminate\Support\Collection;;

/**
 * @property Collection<int, EntryDTO> $items
 */
class EntryCollectionDTO extends BaseDTO
{
    public function __construct(
        public Collection $items,
        public ?string $top_cursor = null,
        public ?string $bottom_cursor = null,
    )
    {
    }
}
