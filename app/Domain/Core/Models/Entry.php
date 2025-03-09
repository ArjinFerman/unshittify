<?php

namespace App\Domain\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Entry extends Model
{
    protected $table = 'core_entries';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'author_id',
        'feed_id',
        'entryable_id',
        'entryable_type',
        'type',
        'url',
        'title',
        'content',
    ];

    public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null): Pivot
    {
        if ($parent instanceof Entry) {
            return EntryReference::fromAttributes($parent, $attributes, $table, $exists);
        }

        return parent::newPivot($parent, $attributes, $table, $exists);
    }

    public function getEntryType(): string
    {
        return class_basename($this->entryable_type);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function entryable(): MorphTo
    {
        return $this->morphTo();
    }

    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'core_entry_references', 'entry_id', 'ref_entry_id')
            ->withPivot('ref_type');
    }

    public function referencedBy(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'core_entry_references', 'ref_entry_id', 'entry_id')
            ->withPivot('ref_type');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
}
