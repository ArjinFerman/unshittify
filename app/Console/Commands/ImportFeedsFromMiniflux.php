<?php

namespace App\Console\Commands;

use App\Domain\Core\Actions\FindOrCreateAuthorAction;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Models\Feed;
use App\Domain\Twitter\Actions\ImportFeedsFromTweetsAction;
use App\Domain\Twitter\DTO\TweetEntryCollectionDTO;
use App\Domain\Twitter\Services\TwitterService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportFeedsFromMiniflux extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unshittify:import-miniflux-feeds';

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
        $this->output->info(__('Starting feed import from Miniflux'));

        /** @var TwitterService $twitterService */
        $twitterService = app(TwitterService::class);
        $conn = DB::connection(env('MINIFLUX_DB_CONNECTION'));
        $minifluxFeeds = $conn->query()
            ->from('feeds')
            ->get();

        $this->output->info(__('Found :feedCount feeds.', ['feedCount' => $minifluxFeeds->count()]));
        $this->output->progressStart($minifluxFeeds->count());

        foreach ($minifluxFeeds as $minifluxFeed) {
            try {
                $conn->beginTransaction();
                $this->output->progressAdvance();
                if (!Str::contains($minifluxFeed->site_url, env('MINIFLUX_TWITTER_HOST')) || $minifluxFeed->disabled)
                    continue;

                $feedUrl = Str::replace(env('MINIFLUX_TWITTER_HOST'), config('twitter.base_url'), $minifluxFeed->site_url);
                $feed = Feed::whereUrl($feedUrl)->first();

                if (!$feed) {
                    $feedName = explode('/', Str::replace('/with_replies', '', $minifluxFeed->site_url));
                    $feedName = end($feedName);

                    $tweets = $twitterService->getLatestUserTweets($feedName);
                    $tweets = (new TweetEntryCollectionDTO([$tweets->first()]))->keyBy('rest_id');

                    $tweetAuthorFeed = ImportFeedsFromTweetsAction::make()->withoutTransaction()->execute($tweets)->first();
                    /** @var Feed $feed */
                    $feed = $tweetAuthorFeed['feed'];
                }

                $feed->status = FeedStatus::ACTIVE;
                $feed->save();

                $conn->commit();
            } catch (\Exception $e) {
                $conn->rollBack();
                $this->output->error($e->getMessage());
            }
        }

        $this->output->info(__('Done.'));
    }
}
