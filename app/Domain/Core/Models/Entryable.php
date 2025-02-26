<?php

namespace App\Domain\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Entryable extends Model
{
    public function entry(): MorphOne
    {
        return $this->morphOne(Entry::class, 'entryable');
    }
}
