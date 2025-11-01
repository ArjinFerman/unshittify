<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Strategies\FeedSyncStrategy;
use App\Domain\Twitter\Models\TwitterFeed;
use App\Domain\Twitter\Strategies\TwitterSyncStrategy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feed extends Model
{
    protected $table = 'feeds';

    protected $primaryKey = 'composite_id';

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

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
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
