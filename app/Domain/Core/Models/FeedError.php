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

class FeedError extends Model
{
    protected $table = 'feed_errors';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'feed_composite_id',
        'message',
    ];

    protected $casts = [
    ];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }
}
