<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\ReferenceType;
use App\Domain\Core\QueryBuilders\EntryQueryBuilder;
use App\Support\Query\EagerLoadJoinTrait;
use App\Domain\Core\Enums\CoreTagType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Znck\Eloquent\Traits\BelongsToThrough;

class Entry extends Model
{
    use EagerLoadJoinTrait;
    use BelongsToThrough;

    protected $table = 'core_entries';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'author_id',
        'feed_id',
        'url',
        'title',
        'content',
        'type',
        'metadata',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'metadata' => 'array',
    ];

    use EagerLoadJoinTrait {
        newFromBuilder as public fromEagerLoadJoinBuilder;
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new EntryQueryBuilder($query);
    }

    public function newFromBuilder($attributes = [], $connection = null)
    {
        $modelClass = $attributes->type;

        if(!$modelClass) return null;

        $model = new $modelClass;

        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?: $this->connection);
        $model->exists = true;

        $model = $model->setupJoinRelations($model, $attributes = []);

        return $model;
    }

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

    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'core_entry_references', 'entry_id', 'ref_entry_id')
            ->withPivot(['ref_type', 'ref_path']);
    }

    public function referencedBy(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'core_entry_references', 'ref_entry_id', 'entry_id')
            ->withPivot('ref_type');
    }

    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable', 'core_mediables')->withTimestamps();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'core_taggables')->withTimestamps();
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
