<?php

namespace App\Console\Commands;

use App\Domain\Core\Actions\FindOrCreateAuthorAction;
use App\Domain\Core\Actions\ImportEntriesAction;
use App\Domain\Core\DTO\AuthorDTO;
use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Enums\MediaType;
use App\Domain\Core\Models\Feed;
use App\Domain\Legacy\V1\DTO\LegacyEntryCollectionDTO;
use App\Domain\Legacy\V1\DTO\LegacyEntryDTO;
use App\Domain\Twitter\DTO\TweetEntryCollectionDTO;
use App\Domain\Twitter\DTO\TwitterMediaDTO;
use App\Domain\Twitter\DTO\TwitterUserFeedDTO;
use App\Domain\Twitter\Services\TwitterService;
use App\Support\CompositeId;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class MigrateEntriesFromV1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unshittify:migrate-from-v1 {batchSize=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected ?Connection $conn;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->output->info(__('Starting feed import from Miniflux'));

        $batchSize = $this->argument('batchSize');
        /** @var TwitterService $twitterService */
        $twitterService = app(TwitterService::class);
        $this->conn = DB::connection(env('V1_DB_CONNECTION'));
        $this->conn->reconnect();

        $lastImported = Cache::get('v1-last-import-id') ?? 0;
        $totalEntryCount = $this->conn->query()->from('core_entries')->count();

        $this->output->info(__('Found :count entries.', ['count' => $totalEntryCount]));
        $this->output->progressStart($totalEntryCount);

        while ($v1Entries = $this->getEntries($lastImported, $batchSize)) {
            $entryData = $this->getEntryData($v1Entries);
            $parsedEntries = LegacyEntryCollectionDTO::createFromRawDB(
                $v1Entries,
                $entryData['feeds'],
                $entryData['authors'],
                $entryData['avatars'],
                $entryData['media'],
                $entryData['references']
            );

            ImportEntriesAction::make()->withoutTransaction()->execute($parsedEntries);

            $lastImported += $batchSize;
            Cache::set('v1-last-import-id', $lastImported);
            $this->output->progressAdvance($batchSize);
        }


        $this->output->info(__('Done.'));
    }

    /**
     * @param int $offset
     * @param int $batchSize
     * @return Collection<int, stdClass>
     */
    protected function getEntries(int $offset, int $batchSize): Collection
    {
        return $this->conn->query()
            ->from('core_entries')
            ->orderBy('id')
            ->limit($batchSize)
            ->offset($offset)
            ->get();
    }

    protected function getEntryData(Collection $v1Entries): array
    {
        $result = [];
        $result['feeds'] = $this->conn->query()
            ->from('core_feeds')->whereIn('id', $v1Entries->pluck('feed_id'))
            ->get();

        $result['authors'] = $this->conn->query()
            ->from('core_authors')
            ->whereIn('id', $result['feeds']->pluck('author_id'))
            ->get();

        $result['avatars'] = $this->conn->query()
            ->from('core_mediables')
            ->join('core_media', 'core_media.id', '=', 'core_mediables.media_id')
            ->whereIn('mediable_id', $result['feeds']->pluck('author_id'))
            ->where('mediable_type', '=', 'App\\Domain\\Core\\Models\\Author')
            ->get();

        $result['media'] = $this->conn->query()
            ->from('core_mediables')
            ->join('core_media', 'core_media.id', '=', 'core_mediables.media_id')
            ->whereIn('mediable_id', $v1Entries->pluck('id'))
            ->where('mediable_type', '=', 'App\\Domain\\Twitter\\Models\\Tweet')
            ->get();

        $result['references'] = $this->conn->query()
            ->from('core_entry_references')
            ->join('core_entries', 'core_entries.id', '=', 'core_entry_references.ref_entry_id')
            ->whereIn('entry_id', $v1Entries->pluck('id'))
            ->get();

        return $result;
    }
}
