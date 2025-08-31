<div class="content-box @if ($level <= 0) content-bg @endif @if ($displayEntry->reply_to_id_str) ml-6 @endif">
    <a href="{{ route('twitter.tweet', ['screenName' => $displayEntry->author?->screen_name, 'tweetId' => $displayEntry->rest_id])  }}"
       class="absolute top-0 left-0 h-full w-full">
    </a>
    <div class="w-full">
        <div class="flex">
            <div class="flex lg:mr-6 mt-1 mr-4 size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                <a href="{{ route('twitter.user', ['screenName' => $displayEntry->author?->screen_name]) }}" class="relative">
                    <img src="{{ $displayEntry->author?->profile_image_url_https }}" alt="{{ $displayEntry->author?->screen_name }}" class="max-h-96 mt-4 rounded-full size-12 sm:size-16">
                </a>
            </div>
            <div class="flex lg:mt-5">
                <a href="{{ route('twitter.user', ['screenName' => $displayEntry->author?->screen_name]) }}" class="relative">
                    <h2 class="text-xl font-semibold text-black dark:text-white">
                        {{ $displayEntry->author?->name }} ({{ "@{$displayEntry->author?->screen_name}" }})
                    </h2>
                </a>
            </div>
        </div>

        <div class="pt-3 sm:pt-5 text-gray-400">
            @if ($isRetweeted)
                <div class="text-sm text-blue-400">
                    <a href="{{ route('twitter.user', ['screenName' => $entry->author?->screen_name]) }}" class="relative">
                        <span>{{ __('twitter.retweeted', ['author' => $entry->author?->name, 'screen_name' => $entry->author?->screen_name]) }}</span>
                    </a>
                </div>
            @endif
            <div class="mt-0 mb-2 text-xs/relaxed">
                <span class="font-bold">{{ __('Published at') }}:</span> <span>{{ $entry->created_at->setTimezone('Europe/Paris') }}</span>
            </div>
            <livewire:menu.tweet-dto :rest_id="$entry->rest_id" :url="$displayEntry->getTweetUrl()" :key="$entry->rest_id" />

            <div class="relative">
                <div class="mt-4 mb-4 text-sm text-white relative" style="overflow-wrap: anywhere">
                    {!! nl2br($displayEntry->full_text) !!}
                </div>

                @foreach ($entry->links ?? [] as $link)
                <x-entry.link :url="$link->expanded_url" />
                @endforeach

                @foreach ($entry->media ?? [] as $media)
                <x-media.media-dto :media="$media" />
                @endforeach
            </div>
        </div>

        @if ($displayEntry->quoted_tweet)
            <x-entry.tweet-dto :entry="$displayEntry->quoted_tweet" :level="$level+1" />
        @endif
    </div>
</div>
