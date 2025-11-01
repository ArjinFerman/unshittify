<?php

namespace App\Domain\Core\Traits\DTO;

trait HasMetadata
{
    public function __get(string $name)
    {
        if (!property_exists($this, $name)) {
            return $this->metadata[$name];
        }

        return $this->{$name};
    }

    public function __set(string $name, $value)
    {
        if (!property_exists($this, $name)) {
            return $this->metadata[$name] = $value;
        }

        $this->{$name} = $value;
    }
}
