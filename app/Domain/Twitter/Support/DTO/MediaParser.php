<?php

namespace App\Domain\Twitter\Support\DTO;

use App\Domain\Core\DTO\MediaCollectionDTO;
use App\Domain\Core\DTO\MediaDTO;
use App\Domain\Core\Enums\MediaType;

class MediaParser
{
    public static function getMediaType(array $media): ?MediaType
    {
        return match ($media['type']) {
            'video' => MediaType::VIDEO,
            'photo' => MediaType::IMAGE,
            default => null,
        };
    }

    public static function mediaDTOCollectionFromTwitter(array $media): MediaCollectionDTO
    {
        $mediaItems = new MediaCollectionDTO;
        $type = self::getMediaType($media);

        switch ($type) {
            case MediaType::VIDEO:
                for ($i = 0; $i < count($media['video_info']['variants']); $i++) {
                    $mediaItems->add(self::videoMediaDTOFromTwitter($media, $i));
                }
                break;
            case MediaType::IMAGE:
                $mediaItems->add(new MediaDTO(
                    variant_id: "twitter-{$media['id_str']}",
                    type: $type,
                    url: $media['media_url_https'],
                    content_type: mimeType($media['media_url_https']),
                    quality: 1,
                    properties: $media['original_info'] ?? null,
                ));
                break;
            default:
                throw new \InvalidArgumentException('Unsupported media type');
        }

        return $mediaItems;
    }

    public static function videoMediaDTOFromTwitter(array $data, int $variantIndex): MediaDTO
    {
        $properties = self::getVideoProperties($data, $variantIndex);

        return new MediaDTO(
            variant_id: "twitter-{$data['id_str']}",
            type: self::getMediaType($data),
            url: $data['video_info']['variants'][$variantIndex]['url'],
            content_type: $data['video_info']['variants'][$variantIndex]['content_type'],
            quality: $properties['quality'],
            properties: $properties['custom'] ?? null,
        );
    }

    protected static function getVideoProperties(array $data, int $variantIndex): array
    {
        $variant = $data['video_info']['variants'][$variantIndex];
        switch ($variant['content_type']) {
            case 'application/x-mpegURL':
                return [
                    'quality' => 0,
                ];
            case 'video/mp4':
                preg_match('/\/(\d+)x(\d+)\//', $variant['url'], $matches);
                return [
                    'quality' => (int)round($variant['bitrate'] / 432000.0),
                    'custom' => [
                        'width' => $matches[1],
                        'height' => $matches[2],
                        'bitrate' => $variant['bitrate'],
                    ]
                ];
        }

        return ['quality' => 0];
    }
}
