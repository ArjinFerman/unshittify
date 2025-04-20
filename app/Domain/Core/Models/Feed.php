<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\FeedType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
