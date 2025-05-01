<a href="{{ $url }}" target="_blank">
    @if ($link)
    <div class="content-box mt-2">
        <div class="pt-3 sm:pt-5">
            <div>
                <h2 class="text-xl font-semibold text-black dark:text-white">
                    {{ $link->feed?->author?->name }}
                </h2>
            </div>

            <div class="mt-4 mb-4 text-sm/relaxed">
                {{ $link->title }}
            </div>
        </div>
    </div>
    @else
    <span class="font-semibold text-blue-600 dark:text-sky-400 underline">
        {{ $url }}
    </span>
    @endif
</a>
