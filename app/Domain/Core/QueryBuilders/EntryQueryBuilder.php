<?php

namespace App\Domain\Core\QueryBuilders;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Models\Feed;
use Illuminate\Support\Facades\DB;
use Staudenmeir\LaravelCte\Query\Builder as StaudenmeirBuilder;

class EntryQueryBuilder extends StaudenmeirBuilder
{
    public function entriesWithReferences(callable $entryQuery, callable $referenceQuery)
    {
        $recursiveQuery = Entry::query()
            ->tap($entryQuery)
            ->select(
                'entries.*',
                DB::raw('0 as `depth`'),
                DB::raw('CAST(`entries`.`composite_id` AS CHAR(2048)) as `path`'),
                DB::raw('CAST(NULL AS CHAR(255)) as `pivot_entry_composite_id`'),
                DB::raw('CAST(NULL AS CHAR(255)) as `pivot_ref_entry_composite_id`'),
                DB::raw('CAST(NULL AS CHAR(255)) as `pivot_ref_type`'),
            )->unionAll(
                Entry::select(
                    'entries.*',
                    DB::raw('`depth` + 1 as `depth`'),
                    DB::raw('CONCAT(`path`, \'.\', `entries`.`composite_id`) as `path`'),
                    DB::raw('`entry_references`.`entry_composite_id` as `pivot_entry_composite_id`'),
                    DB::raw('`entry_references`.`ref_entry_composite_id` as `pivot_ref_entry_composite_id`'),
                    DB::raw('`entry_references`.`ref_type` as `pivot_ref_type`'),
                )->join('entry_references', 'entry_references.ref_entry_composite_id', '=', 'entries.composite_id')
                    ->join('tree', 'tree.composite_id', '=', 'entry_references.entry_composite_id')
                    ->tap($referenceQuery)
                    ->where('depth', '<', 5)
            );


        return $this->from('tree')
            ->withRecursiveExpression('tree', $recursiveQuery);
    }
}
