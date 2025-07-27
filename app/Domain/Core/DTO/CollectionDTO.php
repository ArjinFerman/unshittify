<?php

namespace App\Domain\Core\DTO;

use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 *
 * @template-covariant TValue
 *
 * @implements Collection<TKey, TValue>
 */
class CollectionDTO extends Collection implements \Livewire\Wireable
{
    protected static ?string $class = BaseDTO::class;

    public function toLivewire(): array
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            if ($value instanceof \Livewire\Wireable) {
                $result[$key] = $value->toLivewire();
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public static function fromLivewire($value): static
    {
        $result = new static();
        foreach ($value as $key => $item) {
            $result[$key] = static::$class::fromLivewire($item);
        }

        return $result;
    }
}
