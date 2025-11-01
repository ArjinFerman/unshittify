<?php

namespace App\Domain\Twitter\Services;

use App\Domain\Core\Models\Entry;
use App\Domain\Twitter\Actions\ImportTweetsAction;
use App\Domain\Twitter\DTO\TweetEntryCollectionDTO;
use App\Domain\Twitter\DTO\TweetEntryDTO;
use Illuminate\Support\Collection;
use Exception;
use App\Domain\Twitter\DTO\TwitterUserFeedDTO;
use App\Support\OAuth\OAuth1Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TwitterService
{
    protected string $apiBaseUrl;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected array $accounts = [];

    public function __construct()
    {
        $this->apiBaseUrl = config('twitter.api_base_url');
        $this->consumerKey = config('twitter.consumer_key');
        $this->consumerSecret = config('twitter.consumer_secret');

        $this->loadAccounts();
    }

    protected function loadAccounts(): void
    {
        $handle = Storage::disk('local')->readStream('guest_accounts.jsonl');
        while (!feof($handle)) {
            $this->accounts[] = json_decode(stream_get_line($handle, 0), true);
        }

        fclose($handle);
    }

    /**
     * @param TweetEntryCollectionDTO $tweets
     * @throws \Throwable
     */
    public function importTweets(TweetEntryCollectionDTO $tweets): void
    {
        ImportTweetsAction::make()->execute($tweets);
    }

    public function getUserByScreenName(string $screenName): TwitterUserFeedDTO
    {
        $user = Cache::get("twitter:user:$screenName");
        if ($user)
            return TwitterUserFeedDTO::from($user);

        $variables = json_encode([
            "screen_name" => $screenName,
        ]);

        $userData = $this->fetchImpl($this->apiBaseUrl . config('twitter.endpoints.' . __FUNCTION__), [
            'variables' => $variables,
            'features' => json_encode(config('twitter.gql_features')),
        ]);

        if (!($userData['data']['user_result'] ?? null))
            throw new Exception(__('Could not find user by screenname: :screenName', ['screenName' => $screenName]));

        $user = TwitterUserFeedDTO::createFromUserResult($userData['data']['user_result']);

        Cache::put("twitter:user:$screenName", $user->toArray(), now()->addHours(6));

        return $user;
    }

    public function getLatestUserTweets(string $screenName, ?string $after = null): TweetEntryCollectionDTO
    {
        return $this->getLatestUserTweetsImpl(__FUNCTION__, $screenName, $after);
    }

    public function getLatestUserTweetsAndReplies(string $screenName, ?string $after = null): TweetEntryCollectionDTO
    {
        return $this->getLatestUserTweetsImpl(__FUNCTION__, $screenName, $after);
    }

    protected function getLatestUserTweetsImpl(string $method, string $screenName, ?string $after = null): TweetEntryCollectionDTO
    {
        $cacheKey = "twitter:user:$screenName:$after";
        $tweets = Cache::get($cacheKey);
        if ($tweets) {
            $tweets = TweetEntryCollectionDTO::from($tweets);
            return $tweets;
        }

        $user = $this->getUserByScreenName($screenName);

        $variables = [
            "rest_id" => $user->composite_id->externalId,
            "count" => 20,
        ];

        if ($after)
            $variables["cursor"] = $after;

        $tweets = TweetEntryCollectionDTO::createFromTimelineResult($this->fetchImpl($this->apiBaseUrl . config('twitter.endpoints.' . $method), [
            'variables' => json_encode($variables),
            'features' => json_encode(config('twitter.gql_features')),
        ]));

        $tweets->items = $tweets->items->filter(function (TweetEntryDTO $tweet) use ($user) {
            return $tweet->feed->composite_id->externalId == $user->composite_id->externalId;
        });

        Cache::put($cacheKey, $tweets->toArray(), now()->addMinute());

        return $tweets;
    }

    public function getTweetWithReplies(string $id, ?string $after = null): TweetEntryCollectionDTO
    {
        $cacheKey = "twitter:tweet:$id:$after";
        $tweets = Cache::get($cacheKey);
        if ($tweets)
            return TweetEntryCollectionDTO::from($tweets);

        $variables = config('twitter.tweet_variables');
        $variables['focalTweetId'] = $id;

        if ($after)
            $variables["cursor"] = $after;

        $tweets = TweetEntryCollectionDTO::createFromConversationResult($this->fetchImpl($this->apiBaseUrl . config('twitter.endpoints.' . __FUNCTION__), [
            'variables' => json_encode($variables),
            'features' => json_encode(config('twitter.gql_features')),
        ]));

        $firstTweet = $tweets->items->first();
        $tweets->items = $tweets->items->filter(function (TweetEntryDTO $tweet) use ($firstTweet) {
            return $tweet?->conversation_id_str == $firstTweet?->conversation_id_str;
        });

        Cache::put($cacheKey, $tweets->toArray(), now()->addMinute());

        return $tweets;
    }

    /**
     * @throws Exception
     */
    protected function fetchImpl($url, $parameters = [], $headers = []): array
    {
        $account = $this->accounts[array_rand($this->accounts)];

        if ( empty($account['oauth_token']) )
            throw new Exception('[accounts] Empty oauth token');

        $url = url()->query($url, $parameters);
        $authHeader = OAuth1Client::getOAuthHeader(
            $url,
            $account['oauth_token'], $account['oauth_token_secret'],
            $this->consumerKey, $this->consumerSecret
        );

        $headers = array_merge($headers, [
            "connection" => "keep-alive",
            "authorization" => $authHeader,
            "content-type" => "application/json",
            "x-twitter-active-user" => "yes",
            "authority" => "api.twitter.com",
            'accept-encoding' => 'gzip',
            "accept-language" => "en-US,en;q=0.9",
            "accept" => "*/*",
            "DNT" => "1",
        ]);

        $response = Http::withHeaders($headers)
            ->get($url);

        return $response->json();
    }

}
