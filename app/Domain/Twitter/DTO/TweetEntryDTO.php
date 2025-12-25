<?php

namespace App\Domain\Twitter\DTO;

use App\Domain\Core\DTO\EntryDTO;
use App\Domain\Core\DTO\EntryReferenceDTO;
use App\Domain\Core\DTO\TagDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Enums\ReferenceType;
use App\Support\CompositeId;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property string $conversation_id_str
 * @property string $quoted_status_id_str
 * @property string $reply_to_id_str
 * @property Collection<int, EntryReferenceDTO> $references
 * @property Collection<int, TwitterMediaDTO> $media
 * @property Collection<int, TagDTO> $tags
 */
class TweetEntryDTO extends EntryDTO
{
    public static function createFromTweetResult(array $data): self
    {
        if (!isset($data['rest_id'])) {
            // Case of `$data['__typename'] == 'TweetWithVisibilityResults'`, but there might be more in the future
            $data = $data['tweet'];
        }

        $compositeId = CompositeId::create(ExternalSourceType::TWITTER, $data['rest_id']);
        $references = new Collection();
        if (!empty($data['quoted_status_result'])) {
            $quoted_tweet = TweetEntryDTO::createFromTweetResult($data['quoted_status_result']['result']);
            $references->add(new EntryReferenceDTO(
                entry_composite_id: $compositeId,
                ref_entry_composite_id: $quoted_tweet->composite_id,
                ref_type: ReferenceType::QUOTE,
                referenced_entry: $quoted_tweet,
            ));
        }

        if (!empty($data['legacy']['retweeted_status_result'])) {
            $retweet = TweetEntryDTO::createFromTweetResult($data['legacy']['retweeted_status_result']['result']);
            $references->add(new EntryReferenceDTO(
                entry_composite_id: $compositeId,
                ref_entry_composite_id: $retweet->composite_id,
                ref_type: ReferenceType::REPOST,
                referenced_entry: $retweet,
            ));
        }

        if (!empty($data['legacy']['in_reply_to_status_id_str'])) {
            // HACK: Since it's possible to get the reply tweet without it's parent over the API,
            // we just store an empty reference here
            $references->add(new EntryReferenceDTO(
                entry_composite_id: CompositeId::create(ExternalSourceType::TWITTER, $data['legacy']['in_reply_to_status_id_str']),
                ref_entry_composite_id: $compositeId,
                ref_type: ReferenceType::REPLY_FROM,
                referenced_entry: null,
            ));
        }

        $tweetCard = [];
        if ($cardData = $data['tweet_card'] ?? $data['card'] ?? null) {
            foreach ($cardData['legacy']['binding_values'] as $binding_value) {
                $tweetCard[$binding_value['key']] = $binding_value['value'];
            }
        }

        $feed = TwitterUserFeedDTO::createFromUserResult($data['core']['user_result'] ?? $data['core']['user_results']);

        $content = e(htmlspecialchars_decode($data['note_tweet']['note_tweet_results']['result']['text'] ?? $data['legacy']['full_text']));

        $entities = $data['note_tweet']['note_tweet_results']['result']['entity_set'] ?? $data['legacy']['entities'];
        foreach ($entities['urls'] as $linkData) {
            if ($references->where('referenced_entry.url', $linkData['url'])->count() > 0)
                continue;

            $link = TwitterLinkEntryDTO::createFromTweetResult($linkData, $tweetCard);
            if ($link) {
                $references->add(new EntryReferenceDTO(
                    entry_composite_id: $link->composite_id,
                    ref_entry_composite_id: $compositeId,
                    ref_type: ReferenceType::LINK,
                    referenced_entry: $link,
                ));
                $content = Str::replace($linkData['url'], "<x-entry.link compositeId=\"$link->composite_id\"/>", $content);
            }
        }

        foreach ($entities['user_mentions'] ?? [] as $mention) {
            $mentionId = CompositeId::create(ExternalSourceType::TWITTER, $mention['id_str']);
            $link = "<x-mention compositeId=\"$mentionId\" feedName=\"{$mention['screen_name']}\"/>";
            $content = Str::replace("@{$mention['screen_name']}", $link, $content);
        }

        $mediaCollection = new Collection();
        foreach ($data['legacy']['extended_entities']['media'] ?? [] as $media) {
            $mediaData = TwitterMediaDTO::createFromMedia($media);
            if($mediaData) {
                $mediaCollection->add($mediaData);

                if (Str::contains($content, $media['url']))
                    $content = Str::replace($media['url'], "<x-media compositeId=\"{$mediaData->composite_id}\"/>", $content);
                else
                    $content .= "\n<x-media compositeId=\"{$mediaData->composite_id}\"/>";
            }
        }

        return new self(
            composite_id: $compositeId,
            feed_composite_id: $feed->composite_id,
            url: config('twitter.base_url') . "{$feed->name}/status/{$data['rest_id']}",
            title: "@{$feed->name}",
            content: $content,
            published_at: Carbon::parse($data['legacy']['created_at']),
            is_read: false,
            is_starred: false,
            metadata: [
                'conversation_id_str' => $data['legacy']['conversation_id_str'] ?? null,
                'quoted_status_id_str' => $data['legacy']['quoted_status_id_str'] ?? null,
                'reply_to_id_str' => $data['legacy']['in_reply_to_status_id_str'] ?? null,
                'retweeted_status_result' => $data['legacy']['retweeted_status_result']['result']['rest_id'] ?? null,
            ],
            feed: $feed,
            references: $references,
            media: $mediaCollection,
            tags: new Collection()
        );
    }
}
