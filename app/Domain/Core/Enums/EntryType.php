<?php

namespace App\Domain\Core\Enums;

enum EntryType: string
{
    case TWEET = 'tweet';
    case SUBSTACK = 'substack';
    case WEB_PAGE = 'web_page';
}
