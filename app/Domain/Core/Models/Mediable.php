<?php

namespace App\Domain\Core\Models;

use App\Support\CompositeIdCast;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Mediable extends Pivot
{
    protected $table = 'mediables';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public static array $pivotColumns = [
        'media_composite_id',
        'mediable_composite_id',
        'mediable_type',
    ];

    protected $casts = [
//        'media_composite_id' => CompositeIdCast::class,
//        'mediable_composite_id' => CompositeIdCast::class,
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
