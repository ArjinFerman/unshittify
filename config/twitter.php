<?php

return [
    'base_url' => 'https://x.com/',
    'status_base_url' => 'https://x.com/status/',

    'api_base_url' => env('TWITTER_API_BASE_URL', 'https://api.twitter.com/'),
    'consumer_key' => env('TWITTER_CONSUMER_KEY', '3nVuSoBZnx6U4vzUxf5w'),
    'consumer_secret' => env('TWITTER_CONSUMER_SECRET', 'Bcs59EFbbsdF6Sl9Ng71smgStWEGwXXKSjYvPVt7qys'),

    'endpoints' => [
        'getUserByScreenName'   => 'graphql/u7wQyGi6oExe8_TRWGMq4Q/UserResultByScreenNameQuery',
        'getLatestUserTweets'   => 'graphql/3JNH4e9dq1BifLxAa3UMWg/UserWithProfileTweetsQueryV2',
        'getTweetWithReplies'   => 'graphql/q94uRCEn65LZThakYcPT6g/TweetDetail',
    ],

    'tweet_variables' => [
        'includeHasBirdwatchNotes'  => false,
        'includePromotedContent'    => false,
        'withBirdwatchNotes'        => false,
        'withVoice'                 => false,
        'withV2Timeline'            => true
    ],

    'gql_features' => [
        "android_graphql_skip_api_media_color_palette" => false,
        "blue_business_profile_image_shape_enabled" => false,
        "c9s_tweet_anatomy_moderator_badge_enabled" => false,
        "creator_subscriptions_subscription_count_enabled" => false,
        "creator_subscriptions_tweet_preview_api_enabled" => true,
        "freedom_of_speech_not_reach_fetch_enabled" => false,
        "graphql_is_translatable_rweb_tweet_is_translatable_enabled" => false,
        "hidden_profile_likes_enabled" => false,
        "highlights_tweets_tab_ui_enabled" => false,
        "interactive_text_enabled" => false,
        "longform_notetweets_consumption_enabled" => true,
        "longform_notetweets_inline_media_enabled" => false,
        "longform_notetweets_richtext_consumption_enabled" => true,
        "longform_notetweets_rich_text_read_enabled" => false,
        "responsive_web_edit_tweet_api_enabled" => false,
        "responsive_web_enhance_cards_enabled" => false,
        "responsive_web_graphql_exclude_directive_enabled" => true,
        "responsive_web_graphql_skip_user_profile_image_extensions_enabled" => false,
        "responsive_web_graphql_timeline_navigation_enabled" => false,
        "responsive_web_media_download_video_enabled" => false,
        "responsive_web_text_conversations_enabled" => false,
        "responsive_web_twitter_article_tweet_consumption_enabled" => false,
        "responsive_web_twitter_blue_verified_badge_is_enabled" => true,
        "rweb_lists_timeline_redesign_enabled" => true,
        "rweb_video_timestamps_enabled" => true,
        "spaces_2022_h2_clipping" => true,
        "spaces_2022_h2_spaces_communities" => true,
        "standardized_nudges_misinfo" => false,
        "subscriptions_verification_info_enabled" => true,
        "subscriptions_verification_info_reason_enabled" => true,
        "subscriptions_verification_info_verified_since_enabled" => true,
        "super_follow_badge_privacy_enabled" => false,
        "super_follow_exclusive_tweet_notifications_enabled" => false,
        "super_follow_tweet_api_enabled" => false,
        "super_follow_user_api_enabled" => false,
        "tweet_awards_web_tipping_enabled" => false,
        "tweet_with_visibility_results_prefer_gql_limited_actions_policy_enabled" => false,
        "tweetypie_unmention_optimization_enabled" => false,
        "unified_cards_ad_metadata_container_dynamic_card_content_query_enabled" => false,
        "verified_phone_label_enabled" => false,
        "vibe_api_enabled" => false,
        "view_counts_everywhere_api_enabled" => false
    ]
];
