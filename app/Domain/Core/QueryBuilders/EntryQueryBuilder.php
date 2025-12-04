<?php

namespace App\Domain\Core\QueryBuilders;

use App\Domain\Core\Models\Entry;
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
                DB::raw('0 as "depth"'),
                DB::raw('cast("entries"."composite_id" as text) as "path"'),
                DB::raw('null as "pivot_entry_composite_id"'),
                DB::raw('null as "pivot_ref_entry_composite_id"'),
                DB::raw('null as "pivot_ref_type"'),
            )->unionAll(
                Entry::select(
                    'entries.*',
                    DB::raw('"depth" + 1 as "depth"'),
                    DB::raw('"path" || \'.\' || "entries"."composite_id" as "path"'),
                    DB::raw('"entry_references"."entry_composite_id" as "pivot_entry_composite_id"'),
                    DB::raw('"entry_references"."ref_entry_composite_id" as "pivot_ref_entry_composite_id"'),
                    DB::raw('"entry_references"."ref_type" as "pivot_ref_type"'),
                )->join('entry_references', 'entry_references.ref_entry_composite_id', '=', 'entries.composite_id')
                    ->join('tree', 'tree.composite_id', '=', 'entry_references.entry_composite_id')
                    ->tap($referenceQuery)
            );


        return $this->from('tree')
            ->withRecursiveExpression('tree', $recursiveQuery);
    }
}
