<?php

namespace App\Domain\Core\Enums;

enum ExternalSourceType: string
{
    case TWITTER = 'twitter';
    case YOUTUBE = 'youtube';
    case SUBSTACK = 'substack';
    case WEB = 'web';
}
