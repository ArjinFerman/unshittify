<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\MediaPurpose;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Author extends Model
{
    protected $table = 'core_authors';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable', 'core_mediables');
    }

    public function avatar(): HasOneThrough
    {
        $q = $this->hasOneThrough(
            Media::class,
            Mediable::class,
            'mediable_id',
            'id',
            'id',
            'media_id'
        )->where('mediable_type', static::class);

        return $q;
    }
}
