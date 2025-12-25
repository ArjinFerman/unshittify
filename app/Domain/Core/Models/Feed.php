<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Strategies\FeedSyncStrategy;
use App\Domain\Core\Traits\Models\HasCompositeId;
use App\Domain\Twitter\Strategies\TwitterSyncStrategy;
use App\Support\CompositeId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feed extends Model
{
    use HasCompositeId;

    protected $table = 'feeds';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'author_id',
        'name',
        'status',
        'url',
        'metadata',
    ];

    protected $casts = [
        'status' => FeedStatus::class,
        'metadata' => 'array',
    ];

    public function getSyncStrategy(): FeedSyncStrategy
    {
        $compositeId = CompositeId::fromString($this->composite_id);
        return match($compositeId->source) {
            ExternalSourceType::TWITTER => app(TwitterSyncStrategy::class, ['feed' => $this]),
            default => null,
        };
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'feed_composite_id');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(FeedError::class, 'feed_composite_id');
    }
}
