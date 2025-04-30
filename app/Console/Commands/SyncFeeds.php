<?php

namespace App\Console\Commands;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Feed;
use Illuminate\Console\Command;

class SyncFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unshittify:sync-feeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feeds = Feed::whereStatus(FeedStatus::ACTIVE)->cursor();

        /** @var Feed $feed */
        foreach ($feeds as $feed) {
            $feed->getSyncStrategy()->sync();
        }
    }
}
