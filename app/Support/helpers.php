<?php

use Illuminate\Support\Str;
use Illuminate\Support\Uri;

if (!function_exists('getCleanUrl')) {
    function getCleanUrl(string $url): string
    {
        $parsedUrl = new Uri($url);
        $query = $parsedUrl->query()->all();

        $bannedParams = config('web.banned_params');
        foreach ($query as $param => $value) {
            if ($bannedParams[$param] ?? false) {
                unset($query[$param]);
            }
        }

        $parsedUrl = $parsedUrl->replaceQuery($query);
        $cleanUrl = $parsedUrl->getUri();

        if(empty($query)) {
            $cleanUrl = Str::replace('?', '', $cleanUrl);
        }

        return $cleanUrl;
    }
}

if (!function_exists('mimeType')) {
    function mimeType(string $url)
    {
        $idx = explode('.', $url);
        $idx = strtolower($idx[count($idx) - 1]);
        $mimet = [
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/msword',
            'xlsx' => 'application/vnd.ms-excel',
            'pptx' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        ];
        if (isset($mimet[$idx])) {
            return $mimet[$idx];
        } else {
            return 'application/octet-stream';
        }
    }
}
