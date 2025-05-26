<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\ReferenceType;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EntryReference extends Pivot
{
    protected $table = 'core_entry_references';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'ref_type',
        'ref_path',
    ];

    protected $casts = [
        'ref_type' => ReferenceType::class,
    ];
}
