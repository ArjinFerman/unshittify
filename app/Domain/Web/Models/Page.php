<?php

namespace App\Domain\Web\Models;

use App\Domain\Core\Models\Entry;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Entry
{
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Page::class);
    }
}
