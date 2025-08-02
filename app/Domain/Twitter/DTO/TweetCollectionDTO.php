<?php

namespace App\Domain\Twitter\DTO;

use App\Domain\Core\DTO\CollectionDTO;
use Illuminate\Support\Str;

/**
 * @implements CollectionDTO<mixed, TweetDTO>
 */
class TweetCollectionDTO extends CollectionDTO
{
    protected static ?string $class = TweetDTO::class;

    public function __construct(
        array $items = [],
        protected ?string $top_cursor = null,
        protected ?string $bottom_cursor = null,
    )
    {
        parent::__construct($items);
    }

    public function getTopCursor(): ?string
    {
        return $this->top_cursor;
    }

    public function getBottomCursor(): ?string
    {
        return $this->bottom_cursor;
    }

    public static function fromTimelineResult(array $data): self
    {
        $collection = new self;
        $cursors = [];

        foreach ($data['data']['user_result']['result']['timeline_response']['timeline']['instructions'] as $instruction) {
            if ($instruction['__typename'] === 'TimelineAddEntries') {
                foreach ($instruction['entries'] as $entry) {
                    switch ($entry['content']['__typename']) {
                        case 'TimelineTimelineItem':
                            $collection->add(TweetDTO::fromTweetResult($entry['content']['content']['tweetResult']['result']));
                            break;
                        case 'TimelineTimelineCursor':
                            $cursors[$entry['content']['cursorType']] = $entry['content']['value'];
                            break;
                    }
                }
            }
        }

        $collection->top_cursor = $cursors['Top'] ?? null;
        $collection->bottom_cursor = $cursors['Bottom'] ?? null;
        return $collection;
    }

    public static function fromConversationResult(array $data): self
    {
        $collection = new self;
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
                                    if (Str::contains($tweetResult['source'], 'advertiser')
                                        || @$tweetResult['core']['user_results']['result']['professional']['professional_type'] == 'Business')
                                        break;

                                    $collection->add(TweetDTO::fromTweetResult($tweetResult));
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
                                        if (Str::contains($tweetResult['source'], 'advertiser')
                                            || @$tweetResult['core']['user_results']['result']['professional']['professional_type'] == 'Business')
                                            break;

                                        $collection->add(TweetDTO::fromTweetResult($tweetResult));
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

        $collection->top_cursor = $cursors['Top'] ?? null;
        $collection->bottom_cursor = $cursors['Bottom'] ?? null;
        return $collection;
    }
}
