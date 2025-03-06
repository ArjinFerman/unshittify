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
            default => null,
        };
    }

    public static function mediaDTOCollectionFromTwitter(array $mediaArray): MediaCollectionDTO
    {
        $mediaItems = new MediaCollectionDTO;
        for ($i = 0; $i < count($mediaArray['video_info']['variants']); $i++) {
            $mediaItems->add(self::mediaDTOFromTwitter($mediaArray, $i));
        }

        return $mediaItems;
    }

    public static function mediaDTOFromTwitter(array $data, int $variantIndex): MediaDTO
    {
        $properties = self::getMediaProperties($data, $variantIndex);

        return new MediaDTO(
            remote_id: $data['id_str'],
            type: self::getMediaType($data),
            url: $data['video_info']['variants'][$variantIndex]['url'],
            content_type: $data['video_info']['variants'][$variantIndex]['content_type'],
            quality: $properties['quality'],
            properties: $properties['custom'] ?? null,
        );
    }

    protected static function getMediaProperties(array $data, int $variantIndex): array
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
