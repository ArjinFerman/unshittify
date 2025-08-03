@extends('layouts.default')

@section('content')
<section>
    <div class="grid gap-6 lg:grid-cols-1 lg:gap-8">
        <livewire:menu.entries :entryIds="$entries->pluck('id')->toArray()" :showMarkAll="true" :showMarkPage="false"/>

        @foreach ($entries as $entry)
            <x-dynamic-component :component="'entry.'.$entry->getEntryType()" :entry="$entry" />
        @endforeach

        <livewire:menu.entries :entryIds="$entries->pluck('id')->toArray()" />
    </div>
</section>
@endsection
