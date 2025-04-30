<?php

namespace App\Domain\Core\Strategies;

interface FeedSyncStrategy
{
    public function sync(): void;
}
