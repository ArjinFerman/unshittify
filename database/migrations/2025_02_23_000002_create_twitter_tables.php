<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('twitter_tweets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('twitter_user_id');
            $table->string('tweet_id');
            $table->string('retweet_id')->nullable();
            $table->string('quoted_tweet_id')->nullable();
            $table->string('reply_to_id')->nullable();
            $table->string('conversation_id')->nullable();
            $table->timestamps();

            $table->foreign('twitter_user_id')->references('id')->on('twitter_users');
            $table->index('tweet_id');
            $table->index('retweet_id');
            $table->index('quoted_tweet_id');
            $table->index('reply_to_id');
            $table->index('conversation_id');
        });

        Schema::create('twitter_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id');
            $table->string('screen_name');
            $table->string('twitter_user_id');
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('core_authors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('twitter_tweets');
        Schema::dropIfExists('twitter_users');
    }
};
