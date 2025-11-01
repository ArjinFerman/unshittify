<?php

namespace App\Domain\Core\DTO;

class AuthorDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $description,
    )
    {
    }
}
