<?php

namespace App\Domain\Core\DTO;

use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Traits\DTO\HasMetadata;
use App\Support\CompositeId;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\UnserializeCast;
use Spatie\LaravelData\Transformers\SerializeTransformer;

class FeedDTO extends BaseDTO
{
    use HasMetadata;

    public function __construct(
        public CompositeId $composite_id,
        public AuthorDTO   $author,
        public string      $name,
        public FeedStatus  $status,
        public string      $url,
        #[WithTransformer(SerializeTransformer::class)]
        #[WithCast(UnserializeCast::class)]
        public array       $metadata = [],
    )
    {
    }
}
