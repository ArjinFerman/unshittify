<?php

namespace App\Domain\Core\Models;

use App\Support\CompositeIdCast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Taggable extends Pivot
{
    protected $table = 'taggables';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public static array $pivotColumns = [
        'tag_id',
        'taggable_composite_id',
        'taggable_type',
    ];

    protected $casts = [
        'taggable_composite_id' => CompositeIdCast::class,
    ];

    public function tags(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }
}
