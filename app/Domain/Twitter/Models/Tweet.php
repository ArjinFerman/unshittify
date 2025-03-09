<?php

namespace App\Domain\Twitter\Models;

use App\Domain\Core\Models\Entryable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tweet extends Entryable
{
    protected $table = 'twitter_tweets';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'twitter_user_id',
        'tweet_id',
        'retweet_id',
        'quoted_tweet_id',
        'reply_to_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'twitter_user_id');
    }

    public function retweet(): BelongsTo
    {
        return $this->belongsTo(Tweet::class, 'retweet_id', 'tweet_id');
    }

    public function quotedTweet(): BelongsTo
    {
        return $this->belongsTo(Tweet::class, 'quoted_tweet_id', 'tweet_id');
    }
}
