@php
    /** @var \App\Domain\Core\DTO\EntryCollectionDTO $entries */
@endphp

@extends('layouts.default')

@section('content')
@if ($entries->items->count() <= 0)
    <style>
        .message {
            --tw-text-opacity: 1;
            --shadow-color: rgba(32,164,64,1.0);
            --shadow-blur-size: 2px;
            text-shadow: 0px 1px var(--shadow-blur-size) var(--shadow-color), 1px 0px var(--shadow-blur-size) var(--shadow-color),
            0px -1px var(--shadow-blur-size) var(--shadow-color), -1px 0px var(--shadow-blur-size) var(--shadow-color);

            color: rgb(71 224 253 / var(--tw-text-opacity, 1));
        }
    </style>


    <div class="relative flex justify-center min-h-[50vh] items-center sm:pt-0">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center pt-8 sm:justify-start sm:pt-0">
                <div class="ml-4 tracking-wider message text-center text-md md:text-lg">
                    {!! nl2br(__("The scrolls are spent, the ink run dry.
                    No further missive graces thine eyes this day.
                    Revel in the hush, the absence of clamor—
                    for silence, too, is a gift most rare.
                    Go forth, and let stillness be thy companion.")) !!}
                </div>
            </div>
        </div>
    </div>
@else
<section>
    <div class="grid gap-6 lg:grid-cols-1 lg:gap-8">
        <livewire:menu.entries :isTop="true" :loadNewestLink="$loadNewestLink ?? null"/>

        @foreach ($entries->items as $entry)
            <x-entry :entry="$entry" />
        @endforeach

        <livewire:menu.entries :loadMoreLink="$loadMoreLink ?? null"
                               :entryIds="($loadMoreLink ?? null) ? [] : $entries->items->map(fn($entry) => (string)$entry->composite_id)->toArray()" />
    </div>
</section>
@endif
@endsection
