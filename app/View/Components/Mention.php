<?php

namespace App\View\Components;

use App\Support\CompositeId;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Mention extends Component
{
    public CompositeId $compositeId;

    /**
     * Create a new component instance.
     */
    public function __construct(public string $feedName, string $compositeId)
    {
        $this->compositeId = CompositeId::fromString($compositeId);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.mention');
    }
}
