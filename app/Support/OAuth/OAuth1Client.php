<?php

namespace App\Support\OAuth;

class OAuth1Client
{
    public static function getOAuthHeader($url, $oauthToken, $oauthTokenSecret, $consumerKey, $consumerSecret): string
    {
        // Percent-encode the URL
        // $encodedUrl = rawurlencode($url);

        // Generate OAuth1 parameters
        $params = [
            'oauth_consumer_key' => $consumerKey,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_nonce' => uniqid(),
            'oauth_token' => $oauthToken,
            'oauth_version' => '1.0',
        ];

        // Generate the signature
        $signature = self::getSignature('GET', $url, '', $params, $consumerSecret, $oauthTokenSecret);

        // Add the signature to the parameters
        $params['oauth_signature'] = $signature;

        // Build the OAuth1 header
        $oauthHeader = 'OAuth ';
        $headerParts = [];
        foreach ($params as $key => $value) {
            $headerParts[] = rawurlencode($key) . '="' . rawurlencode($value) . '"';
        }
        $oauthHeader .= implode(', ', $headerParts);

        return $oauthHeader;
    }

    public static function getSignature($httpMethod, $url, $body, $params, $consumerSecret, $token): string
    {
        // Generate a signature.
        $signatureKey = self::getSignatureKey($consumerSecret, $token);
        $signatureBaseString = self::getSignatureBaseString($httpMethod, $url, $body, $params);

        return base64_encode(hash_hmac('sha1', $signatureBaseString, $signatureKey, true));
    }

    protected static function getSignatureBaseString($httpMethod, $url, $body, $params): string
    {
        // Generate a signature base string.
        $requests = [];
        foreach ($params as $key => $value) {
            $requests[] = [ rawurlencode($key),  rawurlencode($value)];
        }

        $parsed = parse_url($url);
        if (empty($parsed['port'])) {
            $url = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
        } else {
            $url = $parsed['scheme'] . '://' . $parsed['host'] . ':' . $parsed['port'] . $parsed['path'];
        }

        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queries);
            foreach ($queries as $key => $value) {
                $requests[] = [ rawurlencode($key),  rawurlencode($value)];
            }
        }

        parse_str($body, $bodyParams);
        foreach ($bodyParams as $key => $value) {
            $requests[] = [ rawurlencode($key),  rawurlencode($value)];
        }

        usort($requests, function($a, $b) {
            return strcmp($a[0], $b[0]) ?: strcmp($a[1], $b[1]);
        });

        $param = '';
        foreach ($requests as $request) {
            if ($param !== '') {
                $param .= '&';
            }
            $param .= $request[0] . '=' . $request[1];
        }

        return $httpMethod . '&' .  rawurlencode($url) . '&' .  rawurlencode($param);
    }

    protected static function getSignatureKey($consumerKey, $token): string
    {
        // Generate a signature key.
        return  rawurlencode($consumerKey) . '&' .  rawurlencode($token);
    }
}
