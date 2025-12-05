<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\DTO\EntryCollectionDTO;
use App\Domain\Core\Enums\FeedStatus;
use Illuminate\Support\Facades\DB;

class EnrichEntriesWithLocalStateDataAction extends BaseAction
{
    /**
     * @param EntryCollectionDTO $entries
     * @return EntryCollectionDTO
     */
    public function execute(EntryCollectionDTO $entries): EntryCollectionDTO
    {
        $stateData = DB::query()
            ->from('entries')
            ->join('feeds', 'feeds.composite_id', '=', 'entries.feed_composite_id')
            ->select('entries.composite_id', 'entries.is_read', 'entries.is_starred', 'feeds.status')
            ->whereIn('entries.composite_id', $entries->items->pluck('composite_id'))
            ->get()
            ->keyBy('composite_id');

        foreach ($entries->items as $entry) {
            if ($tweetState = $stateData->get((string)$entry->composite_id)) {
                $entry->is_read = $tweetState->is_read;
                $entry->is_starred = $tweetState->is_starred;
                $entry->feed->status = FeedStatus::from($tweetState->status);
            }
        }

        return $entries;
    }
}
