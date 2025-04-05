<?php

namespace App\Domain\Web\Models;

use App\Domain\Core\Models\Entryable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Entryable
{
    protected $table = 'web_pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_content',
        'variant_url',
    ];

    public function getEntryType(): string
    {
        return 'link';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Page::class);
    }
}
