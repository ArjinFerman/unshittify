<?php

namespace App\Domain\Core\Enums;

enum FeedType: string
{
    case TWITTER = 'twitter';
    case YOUTUBE = 'youtube';
    case SUBSTACK = 'substack';
}
