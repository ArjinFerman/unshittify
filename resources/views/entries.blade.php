@extends('layouts.default')

@section('content')
<section>
    <div class="grid gap-6 lg:grid-cols-1 lg:gap-8">
        @foreach ($entries as $entry)
            <x-dynamic-component :component="'entry.'.$entry->getEntryType()" :entry="$entry" />
        @endforeach
    </div>
</section>
@endsection
