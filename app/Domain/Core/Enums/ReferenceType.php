<?php

namespace App\Domain\Core\Enums;

enum ReferenceType: string
{
    case LINK = 'link';
    case QUOTE = 'quote';
    case REPOST = 'repost';
    case REPLY_TO = 'reply_to';
}
