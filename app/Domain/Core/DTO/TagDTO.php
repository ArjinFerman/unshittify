<?php

namespace App\Domain\Core\DTO;

class TagDTO extends BaseDTO
{
    public function __construct(
        public int $id,
        public string $name,
    )
    {
    }
}
