<?php

namespace App\Console\Commands;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Feed;
use App\Domain\Core\Models\FeedError;
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
        $this->output->info(__('Starting feed sync'));
        $feeds = Feed::whereStatus(FeedStatus::ACTIVE)->get();

        $this->output->info(__('Found :feedCount feeds.', ['feedCount' => $feeds->count()]));
        $this->output->progressStart($feeds->count());

        /** @var Feed $feed */
        foreach ($feeds as $feed) {
            try {
                $feed->getSyncStrategy()->sync();
                $this->output->progressAdvance();
            } catch (\Throwable $th) {
                $this->output->error(__('Error syncing feed: :error', ['error' => $th->getMessage()]));

                $error = new FeedError;
                $error->message = $th->getMessage();
                $feed->errors()->save($error);
            }
        }

        $this->output->info(__('Finished'));
    }
}
