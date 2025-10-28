<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Media extends Model
{
    protected $table = 'media';

    protected $primaryKey = 'composite_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'url',
        'content_type',
        'metadata',
    ];

    protected $casts = [
        'type' => MediaType::class,
        'metadata' => 'array',
    ];

    public function entries(): MorphToMany
    {
        return $this->morphedByMany(Entry::class, 'mediable', 'mediables')->withTimestamps();
    }
}
