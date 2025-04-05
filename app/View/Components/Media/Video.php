<?php

namespace App\View\Components\Media;

use App\Domain\Core\Models\Media;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Video extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public string $object_id)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $media = Media::whereObjectId($this->object_id)->orderBy('quality', 'desc')->first();
        return view('components.media.video', ['media' => $media]);
    }
}
