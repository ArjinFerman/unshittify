<?php

namespace App\View\Components\Media;

use App\Domain\Core\Models\Media;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Image extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public string $remote_id)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $media = Media::whereRemoteId($this->remote_id)->orderBy('quality', 'desc')->first();
        return view('components.media.image', ['media' => $media]);
    }
}
