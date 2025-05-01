@extends('layouts.default')

@section('content')
    <section>
        @if (isset($loadNewestLink))
            <a href="{{ $loadNewestLink }}">
                <div class="content-box content-bg mb-4">
                    <div class="pt-3 sm:pt-5 w-full">
                        <span class="font-semibold text-center block text-blue-600 dark:text-sky-400 underline">
                            {{ __('Load newest') }}
                        </span>
                    </div>
                </div>
            </a>
        @endif

        <div class="grid gap-6 lg:grid-cols-1 lg:gap-8">
            @foreach ($entries as $entry)
                <x-dynamic-component :component="'entry.'.$entry->getEntryType()" :entry="$entry" />
            @endforeach
        </div>

        @if (isset($loadMoreLink))
            <a href="{{ $loadMoreLink }}">
                <div class="content-box content-bg mt-4">
                    <div class="pt-3 sm:pt-5 w-full">
                        <span class="font-semibold text-center block text-blue-600 dark:text-sky-400 underline">
                        {{ __('Load more') }}
                        </span>
                    </div>
                </div>
            </a>
        @endif
    </section>
@endsection
