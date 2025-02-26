<?php

namespace App\Domain\Twitter\DTO;

use Illuminate\Support\Collection;

/**
 * @implements Collection<TweetDTO>
 */
class TweetCollectionDTO extends Collection
{
    public static function fromTimelineResult(array $data): self
    {
        $items = [];

        foreach ($data['data']['user_result']['result']['timeline_response']['timeline']['instructions'] as $instruction) {
            if ($instruction['__typename'] === 'TimelineAddEntries') {
                foreach ($instruction['entries'] as $entry) {
                    if (!str_starts_with($entry['entryId'], 'tweet-'))
                        continue;

                    $items[] = TweetDTO::fromTweetResult($entry['content']['content']['tweetResult']);
                }
            }
        }

        return new self (items: $items);
    }


}
