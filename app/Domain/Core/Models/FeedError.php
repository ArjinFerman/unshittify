<?php

namespace App\Domain\Core\Models;

use App\Support\CompositeIdCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
//        'feed_composite_id' => CompositeIdCast::class,
    ];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }
}
