<?php

namespace App\Domain\Core\DTO;

use App\Domain\Core\Enums\MediaType;

class TagDTO extends BaseDTO
{
    public function __construct(
        public int $id,
        public string $name,
    )
    {
    }
}
