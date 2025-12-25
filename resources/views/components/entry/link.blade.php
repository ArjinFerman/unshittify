@if ($link)
<a href="{{ $link->url }}" target="_blank">
    <div class="content-box mt-4">
        <div class="pt-3 sm:pt-5">
            <div>
                <h2 class="text-xl font-semibold text-black dark:text-white">
                    {{ "{$link->feed?->handle}" }}
                </h2>
            </div>

            <div class="mt-4 mb-4 text-sm/relaxed">
                {{ $link->title ?: $link->url }}

                @if($link->thumbnail_url)
                <picture>
                    <img src="{{ $link->thumbnail_url }}" alt="{{ $link->title }}" class="max-h-96 mt-4">
                </picture>
                @endif
            </div>
        </div>
    </div>
</a>
@endif
