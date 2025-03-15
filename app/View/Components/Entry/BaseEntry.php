<?php

namespace App\View\Components\Entry;

use App\Domain\Core\Models\Entry;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;

abstract class BaseEntry extends Component
{
    protected function renderComponents(Entry $entry): string
    {
        $mainContent = nl2br($entry->content);
        return Str::replaceMatches('/<x-(\w+)\.(\w+)(?: (\w+)="([^"]+)")+\/>/', function ($matches) {
            $attr = new ComponentAttributeBag();
            for ($i = 3; $i < count($matches); $i+=2) {
                $attr[$matches[$i]] = $matches[$i + 1];
            }

            return Blade::render($matches[0], ['attributes' => $attr]);
        }, $mainContent);
    }
}
