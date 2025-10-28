<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\Models\Entry;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TagAllAction extends BaseAction
{

    /**
     * @throws \Throwable
     */
    public function execute(int $tagId): void
    {
        $this->optionalTransaction(function () use ($tagId) {
            DB::table('taggables')->insertOrIgnoreUsing(
                ['tag_id', 'taggable_id', 'taggable_type', 'created_at', 'updated_at'],
                function (Builder $query) use ($tagId) {
                    $query->select([
                        DB::raw("$tagId as tag_id"),
                        'entries.id',
                        DB::raw(DB::escape(Entry::class) . " as taggable_type"),
                        DB::raw(DB::escape(Carbon::now()) . " as created_at"),
                        DB::raw(DB::escape(Carbon::now()) . " as updated_at"),
                    ])->from('entries');
                });
        });
    }
}
