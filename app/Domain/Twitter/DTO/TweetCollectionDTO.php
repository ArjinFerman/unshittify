<?php

namespace App\Domain\Twitter\DTO;

use Illuminate\Support\Collection;

/**
 * @implements Collection<TweetDTO>
 */
class TweetCollectionDTO extends Collection
{
    public function __construct(
        array $items = [],
        protected ?string $top_cursor = null,
        protected ?string $bottom_cursor = null,
    )
    {
        parent::__construct($items);
    }

    public static function fromTimelineResult(array $data): self
    {
        $items = [];
        $cursors = [];

        foreach ($data['data']['user_result']['result']['timeline_response']['timeline']['instructions'] as $instruction) {
            if ($instruction['__typename'] === 'TimelineAddEntries') {
                foreach ($instruction['entries'] as $entry) {
                    switch ($entry['content']['__typename']) {
                        case 'TimelineTimelineItem':
                            $items[] = TweetDTO::fromTweetResult($entry['content']['content']['tweetResult']);
                            break;
                        case 'TimelineTimelineCursor':
                            $cursors[$entry['content']['cursorType']] = $entry['content']['value'];
                            break;
                    }
                }
            }
        }

        return new self (items: $items, top_cursor: $cursors['Top'] ?? null, bottom_cursor: $cursors['Bottom'] ?? null);
    }

    public function getTopCursor(): ?string
    {
        return $this->top_cursor;
    }

    public function getBottomCursor(): ?string
    {
        return $this->bottom_cursor;
    }
}
