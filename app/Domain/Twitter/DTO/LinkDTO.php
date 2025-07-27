<?php

namespace App\Domain\Twitter\DTO;

use App\Domain\Core\DTO\BaseDTO;

class LinkDTO extends BaseDTO
{
    public function __construct(
        public string $url,
        public ?string $expanded_url    = null,
        public ?string $author          = null,
        public ?string $title           = null,
        public ?string $description     = null,
        public ?string $thumbnail_url   = null
    )
    {
    }
}
