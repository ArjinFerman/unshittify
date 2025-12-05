<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\Models\Entry;
use App\Support\CompositeId;

class MarkAsStarredAction extends BaseAction
{
    /**
     * @throws \Throwable
     */
    public function execute(CompositeId $compositeId): void
    {
        $this->optionalTransaction(function () use ($compositeId) {
            Entry::query()
                ->where('composite_id', '=', $compositeId)
                ->update(['is_starred' => true]);
        });
    }
}
