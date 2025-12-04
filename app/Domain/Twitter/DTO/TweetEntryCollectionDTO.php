<?php

namespace App\Domain\Twitter\DTO;

use App\Domain\Core\DTO\EntryCollectionDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TweetEntryCollectionDTO extends EntryCollectionDTO
{
    public static function createFromTimelineResult(array $data): self
    {
        $collection = new Collection();
        $cursors = [];

        if (!isset($data['data']))
            Log::error('Timeline result has no data', $data);

        foreach ($data['data']['user_result']['result']['timeline_response']['timeline']['instructions'] as $instruction) {
            if ($instruction['__typename'] === 'TimelineAddEntries') {
                foreach ($instruction['entries'] as $entry) {
                    switch ($entry['content']['__typename']) {
                        case 'TimelineTimelineItem':
                            if ($result = ($entry['content']['content']['tweetResult']['result'] ?? null))
                                $collection->add(TweetEntryDTO::createFromTweetResult($result));
                            else
                                Log::warning(__('TweetResut not found in entry ID: :entryId', ['entryId' => $entry['entryId'] ?? 'No ID']));

                            break;
                        case 'TimelineTimelineCursor':
                            $cursors[$entry['content']['cursorType']] = $entry['content']['value'];
                            break;
                    }
                }
            }
        }

        return new static(
            items: $collection,
            top_cursor: $cursors['Top'] ?? null,
            bottom_cursor: $cursors['Bottom'] ?? null
        );
    }

    public static function createFromConversationResult(array $data): self
    {
        $collection = new Collection();
        $cursors = [];

        foreach ($data['data']['threaded_conversation_with_injections_v2']['instructions'] as $instruction) {
            if ($instruction['type'] === 'TimelineAddEntries') {
                foreach ($instruction['entries'] as $entry) {
                    switch ($entry['content']['__typename']) {
                        case 'TimelineTimelineItem':
                            switch ($entry['content']['itemContent']['__typename']) {
                                case 'TimelineTweet':
                                    if(!isset($entry['content']['itemContent']['tweet_results']['result']))
                                        break;

                                    $tweetResult = $entry['content']['itemContent']['tweet_results']['result'];
                                    $collection->add(TweetEntryDTO::createFromTweetResult($tweetResult));
                                    break;
                                case 'TimelineTimelineCursor':
                                    $cursors[$entry['content']['itemContent']['cursorType']] = $entry['content']['itemContent']['value'];
                                    break;
                            }
                            break;
                        case 'TimelineTimelineModule':
                            foreach ($entry['content']['items'] as $threadItem) {
                                switch ($threadItem['item']['itemContent']['__typename']) {
                                    case 'TimelineTweet':
                                        if (!$threadItem['item']['itemContent']['tweet_results'])
                                            break;

                                        $tweetResult = $threadItem['item']['itemContent']['tweet_results']['result'];
                                        $collection->add(TweetEntryDTO::createFromTweetResult($tweetResult));
                                        break;
                                    // TODO: case 'TimelineTimelineCursor':
                                }
                            }
                            break;
                        case 'TimelineTimelineCursor':
                            $cursors[$entry['content']['cursorType']] = $entry['content']['value'];
                            break;
                    }
                }
            }
        }

        return new static(
            items: $collection,
            top_cursor: $cursors['Top'] ?? null,
            bottom_cursor: $cursors['Bottom'] ?? null
        );
    }
}
