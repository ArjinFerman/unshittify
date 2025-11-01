<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\ReferenceType;
use App\Support\CompositeIdCast;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EntryReference extends Pivot
{
    protected $table = 'entry_references';

    public $timestamps = false;

    public static array $pivotColumns = [
        'entry_composite_id',
        'ref_entry_composite_id',
        'ref_type',
    ];

    protected $fillable = [
        'ref_type',
    ];

    protected $casts = [
        'entry_composite_id' => CompositeIdCast::class,
        'ref_entry_composite_id' => CompositeIdCast::class,
        'ref_type' => ReferenceType::class,
    ];
}
