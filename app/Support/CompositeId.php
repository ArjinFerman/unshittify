<?php

namespace App\Support;

use App\Domain\Core\Enums\ExternalSourceType;
use Livewire\Wireable;

class CompositeId implements Wireable
{
    public function __construct(
        public ExternalSourceType $source,
        public string $externalId,
    )
    {

    }

    public function toLivewire(): string
    {
        return (string)$this;
    }

    public static function fromLivewire($value): static
    {
        return static::fromString($value);
    }

    public static function create(ExternalSourceType $source, string $externalId)
    {
        return new self($source, $externalId);
    }

    public static function fromString(string $compositeId): CompositeId
    {
        [$source, $externalId] = explode('|', $compositeId, 2);
        return self::create(ExternalSourceType::from($source), $externalId);
    }

    public function __toString(): string
    {
        return "{$this->source->value}|{$this->externalId}";
    }
}
