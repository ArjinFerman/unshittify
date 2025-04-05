<?php

namespace App\Domain\Twitter\DTO;

class LinkDTO
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
