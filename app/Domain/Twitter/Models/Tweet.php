<?php

namespace App\Domain\Twitter\Models;

use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Models\Entry;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tweet extends Entry
{
    public function getMorphClass(): string
    {
        return Entry::class;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'twitter_user_id', '');
    }

    public function retweeted(): ?static
    {
        return $this->references
            ->where('ref_type', '=', ReferenceType::REPOST)
            ->first();
    }

    public function quotedTweet(): ?static
    {
        return $this->references
            ->where('ref_type', '=', ReferenceType::QUOTE)
            ->first();
    }

    public function replies(): ?Collection
    {
        return $this->references
            ->where('ref_type', '=', ReferenceType::REPLY_TO);
    }
}
