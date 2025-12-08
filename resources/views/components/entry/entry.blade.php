@php
    /** @var \App\Domain\Core\DTO\EntryDTO $entry */
    /** @var \App\Domain\Core\DTO\EntryDTO $displayEntry */
@endphp
<div class="content-box @if ($level <= 0) content-bg @endif @if ($displayEntry->reply_to_id_str) ml-6 @endif">
    <div class="w-full">
        <div class="flex">
            <div class="flex lg:mr-6 mt-1 mr-4 size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                <a href="{{ route("{$entry->composite_id->source->value}.feed", ['feedName' => $displayEntry->feed?->name]) }}" class="relative">
                    <img src="{{ $displayEntry->feed?->profile_image }}" alt="{{ $displayEntry->feed?->name }}" class="max-h-96 mt-4 rounded-full size-12 sm:size-16">
                </a>
            </div>
            <div class="flex lg:mt-5">
                <a href="{{ route("{$entry->composite_id->source->value}.feed", ['feedName' => $displayEntry->feed?->name]) }}" class="relative">
                    <h2 class="text-xl font-semibold text-black dark:text-white">
                        {{ $displayEntry->feed?->getDisplayName() }}
                    </h2>
                </a>
            </div>
        </div>

        <div class="pt-3 sm:pt-5 text-gray-400">
            @if ($isRetweeted)
                <div class="text-sm text-blue-400">
                    <a href="{{ route("{$entry->composite_id->source->value}.feed", ['feedName' => $displayEntry->feed?->name]) }}" class="relative">
                        <span>{{ __('core.reposted', ['name' => $entry->feed?->getDisplayName()]) }}</span>
                    </a>
                </div>
            @endif
            <div class="mt-0 mb-2 text-xs/relaxed">
                <span class="font-bold">{{ __('Published at') }}:</span> <span>{{ $entry->published_at->setTimezone('Europe/Paris') }}</span>
            </div>
            <livewire:menu.entry :entry="$entry" />

            <div class="relative">
                <div class="mt-4 mb-4 text-sm text-white relative" style="overflow-wrap: anywhere">
                    {!! $displayContent !!}
                </div>
            </div>
        </div>

        @if ($quote = $displayEntry->quote() ?? null)
            <x-entry :entry="$quote" :level="$level+1" />
        @endif
    </div>
</div>
