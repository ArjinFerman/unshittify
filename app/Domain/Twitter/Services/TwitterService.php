<?php

namespace App\Domain\Twitter\Services;

use App\Domain\Core\Models\Entry;
use App\Domain\Twitter\Actions\ImportTweetAction;
use App\Domain\Twitter\Actions\ImportTweetsAction;
use App\Domain\Twitter\DTO\TweetCollectionDTO;
use Illuminate\Support\Collection;
use Exception;
use App\Domain\Twitter\DTO\UserDTO;
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
        $handle = Storage::disk('public')->readStream('guest_accounts.jsonl');
        while (!feof($handle)) {
            $this->accounts[] = json_decode(stream_get_line($handle, 0), true);
        }

        fclose($handle);
    }

    /**
     * @param TweetCollectionDTO $tweets
     * @return Collection<mixed, Entry>
     * @throws \Throwable
     */
    public function importTweets(TweetCollectionDTO $tweets, bool $createTweets = false): Collection
    {
        $entries = ImportTweetsAction::make()->execute($tweets, $createTweets);

        return $entries;
    }

    public function getUserByScreenName(string $screenName): UserDTO
    {
        $user = Cache::get("twitter:user:$screenName");
        if ($user)
            return $user;

        $variables = json_encode([
            "screen_name" => $screenName,
        ]);

        $user = UserDTO::fromUserResult($this->fetchImpl($this->apiBaseUrl . config('twitter.endpoints.' . __FUNCTION__), [
            'variables' => $variables,
            'features' => json_encode(config('twitter.gql_features')),
        ])['data']['user_result']);

        Cache::put("twitter:user:$screenName", $user, now()->addHours(6));

        return $user;
    }

    public function getLatestUserTweets(string $screenName, ?string $after = null): TweetCollectionDTO
    {
        $cacheKey = "twitter:user:$screenName:$after";
        $tweets = Cache::get($cacheKey);
        if ($tweets) return $tweets;

        $user = $this->getUserByScreenName($screenName);

        $variables = [
            "rest_id" => $user->rest_id,
            "count" => 20,
        ];

        if ($after)
            $variables["cursor"] = $after;

        $tweets = TweetCollectionDTO::fromTimelineResult($this->fetchImpl($this->apiBaseUrl . config('twitter.endpoints.' . __FUNCTION__), [
            'variables' => json_encode($variables),
            'features' => json_encode(config('twitter.gql_features')),
        ]));

        Cache::put($cacheKey, $tweets, now()->addMinute());

        return $tweets;
    }

    public function getTweetWithReplies(string $id, ?string $after = null): TweetCollectionDTO
    {
        $cacheKey = "twitter:tweet:$id:$after";
        $tweets = Cache::get($cacheKey);
        if ($tweets) return $tweets;

        $variables = config('twitter.tweet_variables');
        $variables['focalTweetId'] = $id;

        if ($after)
            $variables["cursor"] = $after;

        $tweets = TweetCollectionDTO::fromConversationResult($this->fetchImpl($this->apiBaseUrl . config('twitter.endpoints.' . __FUNCTION__), [
            'variables' => json_encode($variables),
            'features' => json_encode(config('twitter.gql_features')),
        ]));

        Cache::put($cacheKey, $tweets, now()->addMinute());

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
