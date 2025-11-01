<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\Traits\Models\HasCompositeId;
use App\Domain\Core\Enums\CoreTagType;
use App\Support\CompositeIdCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasGraphRelationships;
use Znck\Eloquent\Traits\BelongsToThrough;

class Entry extends Model
{
    use HasCompositeId, BelongsToThrough, HasGraphRelationships;

    protected $table = 'entries';

    public function getPivotTableName(): string
    {
        return 'entry_references';
    }

    public function getPivotColumns(): array
    {
        return ['ref_type'];
    }

    public function getParentKeyName(): string
    {
        return 'entry_composite_id';
    }

    public function getChildKeyName(): string
    {
        return 'ref_entry_composite_id';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'feed_composite_id',
        'url',
        'title',
        'content',
        'published_at',
        'is_read',
        'metadata',
    ];

    protected $casts = [
        'feed_composite_id' => CompositeIdCast::class,
        'published_at' => 'datetime',
        'metadata' => 'array',
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
        return class_basename($this->type);
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function author(): \Znck\Eloquent\Relations\BelongsToThrough
    {
        return $this->belongsToThrough(Author::class, Feed::class, foreignKeyLookup: [Author::class => 'author_id']);
    }

    public function references(): Relation
    {
        return $this->descendants();
    }

    public function referencedBy(): BelongsToMany
    {
        return $this->ancestors();
    }

    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable', 'mediables', 'media_composite_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_composite_id');
    }

    public function displayEntry(): self
    {
        return ($this->references
            ->where('ref_type', ReferenceType::REPOST->value)
            ->first() ?? $this);
    }

    public function isRead(): bool
    {
        return $this->is_read;
    }

    public function isStarred(): bool
    {
        return $this->hasTag(CoreTagType::STARRED->value);
    }

    public function hasTag(int $tagId): bool
    {
        return !is_null($this->tags->firstWhere('id', $tagId));
    }
}
