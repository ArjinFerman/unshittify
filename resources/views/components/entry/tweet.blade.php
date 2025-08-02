<div class="content-box @if ($level <= 0) content-bg @endif">
    <a href="{{ route('twitter.tweet', ['screenName' => $displayEntry->metadata['user']['screen_name'], 'tweetId' => $displayEntry->metadata['tweet_id']])  }}"
       class="absolute top-0 left-0 h-full w-full">
    </a>
    <div class="w-full">
        <div class="flex">
            <div class="flex lg:mr-6 mt-1 mr-4 size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                <a href="{{ route('twitter.user', ['screenName' => $displayEntry->metadata['user']['screen_name']]) }}" class="relative">
                    <x-media :media="$displayEntry->feed?->author?->avatar" class="rounded-full size-12 sm:size-16"/>
                </a>
            </div>
            <div class="flex lg:mt-5">
                <a href="{{ route('twitter.user', ['screenName' => $displayEntry->metadata['user']['screen_name']]) }}" class="relative">
                    <h2 class="text-xl font-semibold text-black dark:text-white">
                        {{ $displayEntry->feed?->author?->name }} ({{ "@{$displayEntry->metadata['user']['screen_name']}" }})
                    </h2>
                </a>
            </div>
        </div>

        <div class="pt-3 sm:pt-5 text-gray-400">
            @if ($isRetweeted)
                <div class="text-sm text-blue-400">
                    <a href="{{ route('twitter.user', ['screenName' => $entry->metadata['user']['screen_name']]) }}" class="relative">
                        <span>{{ __('twitter.retweeted', ['author' => $entry->feed?->author?->name, 'screen_name' => $entry->metadata['user']['screen_name']]) }}</span>
                    </a>
                </div>
            @endif
            <div class="mt-0 mb-2 text-xs/relaxed">
                <span class="font-bold">{{ __('Published at') }}:</span> <span>{{ $entry->published_at->setTimezone('Europe/Paris') }}</span>
            </div>
            <livewire:entry.menu :entry="$entry" :key="$entry->id" />

            <div class="mt-4 mb-4 text-sm text-white relative" style="overflow-wrap: anywhere">
                {!! $displayContent !!}
            </div>
        </div>

        @if ($displayEntry->quotedTweet())
            <x-dynamic-component :component="'entry.'.$displayEntry->getEntryType()" :entry="$displayEntry->quotedTweet()" :level="$level+1" />
        @endif
    </div>
</div>

@foreach ($displayEntry->replies() as $reply)
    <x-dynamic-component :component="'entry.'.$reply->getEntryType()" :entry="$reply" />
@endforeach
