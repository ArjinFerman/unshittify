<?php

namespace App\Domain\Core\Models;

use Illuminate\Database\Eloquent\Model;

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
}
