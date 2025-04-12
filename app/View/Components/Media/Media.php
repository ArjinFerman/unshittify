<?php

namespace App\View\Components\Media;

use App\Domain\Core\Models\Media as MediaModel;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Media extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ?MediaModel $media = null, public ?string $mediaObjectId = null)
    {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if (!$this->media) {
            $this->media = MediaModel::whereMediaObjectId($this->mediaObjectId)
                ->orderBy('quality', 'desc')->first();
        }

        return view('components.media.' . $this->media->type->value, ['media' => $this->media]);
    }
}
