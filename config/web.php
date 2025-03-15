<?php

return [
    'banned_params' => [
        'utm_source' => true,
        'utm_medium' => true,
        'utm_campaign' => true,
        'utm_term' => true,
        'utm_content' => true,
        'taid' => true,
    ],
    'crawler' => [
        'blacklist' => [
            'x.com' => true,
            'youtube.com' => true,
        ],
        'request_headers' => [
            'accept' => '*/*',
            'accept-language' => 'en-US,en;q=0.7',
            'cache-control' => 'no-cache',
            'pragma' => 'no-cache',
            'priority' => 'u=0, i',
            'sec-fetch-site' => 'none',
            'sec-fetch-user' => '?1',
            'sec-gpc' => '1',
            'upgrade-insecure-requests' => '1',
            'user-agent' => 'curl/7.74.0',
        ]
    ]
];
