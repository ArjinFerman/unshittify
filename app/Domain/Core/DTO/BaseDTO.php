<?php

namespace App\Domain\Core\DTO;

class BaseDTO implements \Livewire\Wireable
{
    public function toLivewire(): array
    {
        $result = [];
        foreach ($this as $key => $value) {
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
        foreach ($value as $key => $property) {
            if (is_array($property)) {
                $result->$key->fromLivewire($property);
            } else {
                $result->$key = $property;
            }
        }

        return $result;
    }
}
