<?php

namespace App\Domain\Core\DTO;

/**
 * @implements CollectionDTO<mixed, MediaDTO>
 */
class MediaCollectionDTO extends CollectionDTO
{
    protected static ?string $class = MediaDTO::class;
}
