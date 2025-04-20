<?php

namespace App\Domain\Core\Enums;

enum FeedStatus: string
{
    case PREVIEW = 'preview';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
