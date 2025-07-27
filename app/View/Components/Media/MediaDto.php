<?php

namespace App\View\Components\Media;

use App\Domain\Core\DTO\MediaDTO as APIMediaDTO;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MediaDto extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public APIMediaDTO $media)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.media.' . $this->media->type->value, ['media' => $this->media]);
    }
}
