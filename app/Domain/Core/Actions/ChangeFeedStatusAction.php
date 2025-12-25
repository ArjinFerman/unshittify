<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\DTO\FeedDTO;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Author;
use App\Domain\Core\Models\Feed;

class ChangeFeedStatusAction extends BaseAction
{
    /**
     * @throws \Throwable
     */
    public function execute(FeedDTO $feedData, FeedStatus $status): FeedStatus
    {
        return $this->optionalTransaction(function () use ($feedData, $status) {
            $feed = Feed::find($feedData->composite_id);
            $feed->status = $status;

            $feed->save();
            $feed->refresh();

            return $feed->status;
        });
    }
}
