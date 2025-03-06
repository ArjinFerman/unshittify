<?php

namespace App\Domain\Twitter\Services;

use App\Domain\Core\Models\Entry;
use App\Domain\Twitter\Actions\ImportTweetAction;
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
    protected string $baseUrl;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected array $accounts = [];

    public function __construct()
    {
        $this->baseUrl = config('twitter.base_url');
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
     * @return Collection<Entry>
     * @throws \Throwable
     */
    public function importTweets(TweetCollectionDTO $tweets): Collection
    {
        $entries = new Collection();
        foreach ($tweets as $tweet) {
            $entries->add(ImportTweetAction::make()->execute($tweet));
        }

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

        $user = UserDTO::fromUserResult($this->fetchImpl($this->baseUrl . config('twitter.endpoints.' . __FUNCTION__), [
            'variables' => $variables,
            'features' => json_encode(config('twitter.gql_features')),
        ])['data']['user_result']);

        Cache::put("twitter:user:$screenName", $user, now()->addHours(6));

        return $user;
    }

    public function getLatestUserTweets(string $screenName): TweetCollectionDTO
    {
        $user = $this->getUserByScreenName($screenName);

        $tweets = Cache::get("twitter:latest_tweets:$screenName");
        if ($tweets) return $tweets;

        $variables = json_encode([
            "rest_id" => $user->rest_id,
            "count" => 20,
        ]);

        $tweets = TweetCollectionDTO::fromTimelineResult($this->fetchImpl($this->baseUrl . config('twitter.endpoints.' . __FUNCTION__), [
            'variables' => $variables,
            'features' => json_encode(config('twitter.gql_features')),
        ]));

        Cache::put("twitter:latest_tweets:$screenName", $tweets, now()->addMinute());

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
