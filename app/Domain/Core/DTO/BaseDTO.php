<?php

namespace App\Domain\Core\DTO;

use Livewire\Wireable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;

class BaseDTO extends Data implements Wireable
{
    public static function factory(?CreationContext $creationContext = null): CreationContextFactory
    {
        return parent::factory($creationContext)->ignoreMagicalMethod('fromLivewire');
    }

    public function toLivewire(): array
    {
        return $this->toArray();
    }

    public static function fromLivewire($value): static
    {
        return static::from($value);
    }
}
