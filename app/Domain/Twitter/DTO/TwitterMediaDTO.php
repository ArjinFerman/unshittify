<?php

namespace App\Domain\Twitter\DTO;

use App\Domain\Core\DTO\MediaDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Enums\MediaType;
use App\Support\CompositeId;
use Illuminate\Support\Facades\Log;

class TwitterMediaDTO extends MediaDTO
{
    public static function createFromMedia(array $media): ?TwitterMediaDTO
    {
        $type = self::getMediaType($media);

        switch ($type) {
            case MediaType::VIDEO:
                return self::videoMediaDTOFromTwitter($media);
            case MediaType::IMAGE:
                return new self(
                    composite_id: CompositeId::create(ExternalSourceType::TWITTER, $media['id_str']),
                    type: $type,
                    url: $media['media_url_https'],
                    content_type: mimeType($media['media_url_https']),
                    metadata: $media['original_info'] ?? null,
            );
        }

        Log::warning("Unsupported media type: {$media['type']}");
        return null;
    }

    public static function videoMediaDTOFromTwitter(array $data): self
    {
        $variant = null;
        $metadata = ['variants' => []];
        foreach ($data['video_info']['variants'] as $variant) {
            $metadata['variants'][] = self::getVideoProperties($variant);
        }

        return new self(
            composite_id: CompositeId::create(ExternalSourceType::TWITTER, $data['id_str']),
            type: self::getMediaType($data),
            url: $variant['url'],
            content_type: $variant['content_type'],
            metadata: $metadata,
        );
    }

    protected static function getVideoProperties(array $variant): array
    {
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

    public static function getMediaType(array $media): ?MediaType
    {
        return match ($media['type']) {
            'video' => MediaType::VIDEO,
            'photo' => MediaType::IMAGE,
            default => null,
        };
    }
}
