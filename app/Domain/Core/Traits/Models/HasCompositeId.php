<?php

namespace App\Domain\Core\Traits\Models;

use App\Support\CompositeIdCast;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasCompositeId
{
    protected function initializeHasCompositeId()
    {
        $this->casts['composite_id'] = CompositeIdCast::class;
        $this->primaryKey = 'composite_id';
        $this->keyType = 'string';
    }
}
