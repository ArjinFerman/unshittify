<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Feed;

class ChangeFeedStatusAction extends BaseAction
{
    /**
     * @throws \Throwable
     */
    public function execute(Feed $feed, FeedStatus $status): void
    {
        $this->optionalTransaction(function () use ($feed, $status) {
            $feed->status = $status;
            $feed->save();
        });
    }
}
