<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\FeedType;
use App\Domain\Core\Strategies\FeedSyncStrategy;
use App\Domain\Twitter\Models\TwitterFeed;
use App\Domain\Twitter\Strategies\TwitterSyncStrategy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feed extends Model
{
    protected $table = 'core_feeds';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'author_id',
        'name',
        'type',
        'status',
        'url',
    ];

    protected $casts = [
        'type' => FeedType::class,
        'status' => FeedStatus::class,
    ];

    public function getSyncStrategy(): FeedSyncStrategy
    {
        return match($this->type) {
            FeedType::TWITTER => app(TwitterSyncStrategy::class, ['feed' => $this]),
            default => null,
        };
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'feed_id');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(FeedError::class, 'feed_id');
    }
}
