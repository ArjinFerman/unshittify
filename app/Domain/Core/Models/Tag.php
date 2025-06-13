<?php

namespace App\Domain\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    protected $table = 'core_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    public function authors(): MorphToMany
    {
        return $this->morphToMany(Author::class, 'taggable', 'core_taggables', 'taggable_id');
    }

    public function entries(): MorphToMany
    {
        return $this->morphedByMany(Entry::class, 'taggable', 'core_taggables');
    }
}
