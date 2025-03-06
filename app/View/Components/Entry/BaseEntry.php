<?php

namespace App\View\Components\Entry;

use App\Domain\Core\Models\Entry;
use Illuminate\Support\Str;
use Illuminate\View\Component;

abstract class BaseEntry extends Component
{
    protected function renderMedia(Entry $entry): string
    {
        $media = $entry->media
            ->sortByDesc(fn($item, $key) => $item['quality'])
            ->groupBy('remote_id');

        return Str::replaceMatches('/<x-media.(\w+)(?: :(\w+)="(.*)")+\/>/', function ($matches) use ($media) {
            $data = [];
            $mediaType = $matches[1];
            for ($i = 2; $i < count($matches); $i+=2) {
                $data[$matches[$i]] = $matches[$i + 1];
            }

            if (!isset($data['remote_id']))
                return '';

            $data['media'] = $media[$data['remote_id']]->first();
            return view("components.media.$mediaType", $data)->render();
        }, $entry->content);
    }
}
