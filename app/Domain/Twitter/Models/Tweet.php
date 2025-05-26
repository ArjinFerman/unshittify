<?php

namespace App\Domain\Twitter\Models;

use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tweet extends Entry
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'twitter_user_id', '');
    }

    public function retweeted(): ?static
    {
        return $this->references
            ->where('pivot.ref_type', '=', ReferenceType::REPOST)
            ->first();
    }

    public function quotedTweet(): ?static
    {
        return $this->references
            ->where('pivot.ref_type', '=', ReferenceType::QUOTE)
            ->first();
    }

    public function replyToTweet(): ?static
    {
        return $this->references
            ->where('ref_type', '=', ReferenceType::REPLY_TO)
            ->first();
    }
}
