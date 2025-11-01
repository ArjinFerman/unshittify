<?php

namespace App\Domain\Core\DTO;

use App\Support\CompositeId;

class MediableDTO extends BaseDTO
{
    public function __construct(
        public CompositeId $media_composite_id,
        public CompositeId $mediable_composite_id,
        public string $mediable_type,
    )
    {
    }
}
