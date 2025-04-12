<div class="flex items-start gap-4 rounded-lg bg-white p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05]
    transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-7 dark:bg-zinc-900
    dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
    <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
        <!-- <svg class="size-5 sm:size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"> -->
        <x-media.image :mediaObjectId="$mainEntry->author?->avatars()->first()?->media_object_id" class="rounded-full size-12 sm:size-16"/>
    </div>

    <div class="pt-3 sm:pt-5">
        <div>
            <h2 class="text-xl font-semibold text-black dark:text-white">
                {{ $mainEntry->author?->name }} ({{ "@{$mainEntry->entryable->user->screen_name}" }})
            </h2>
            @if ($isRetweeted)
            <span>{{ __('twitter.retweeted', ['author' => $entry->author?->name, 'screen_name' => $entry->entryable->user->screen_name]) }}</span>
            @endif
        </div>

        <div class="mt-4 mb-4 text-sm/relaxed">
            {!! $mainContent !!}
        </div>

        @if ($mainEntry->entryable->quoted_tweet_id)
        <x-dynamic-component :component="'entry.'.$mainEntry->getEntryType()" :entry="$mainEntry->entryable->quotedTweet->entry" />
        @endif
    </div>
</div>
