<?php

namespace App\Support;

use App\Domain\Core\Enums\ExternalSourceType;

class CompositeId
{
    public function __construct(
        public ExternalSourceType $source,
        public string $externalId,
    )
    {

    }

    public static function create(ExternalSourceType $source, string $externalId)
    {
        return new self($source, $externalId);
    }

    public function __toString(): string
    {
        return "{$this->source->value}|{$this->externalId}";
    }
}
