<?php

namespace App\Domain\Core\DTO;

use App\Domain\Core\Enums\MediaType;

class MediaDTO
{
    public function __construct(
        public string $media_object_id,
        public MediaType $type,
        public string $url,
        public string $content_type,
        public int $quality,
        public ?array $properties = null,
    )
    {
    }
}
