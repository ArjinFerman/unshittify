<video controls preload="metadata" {{ $attributes->merge(['class' => 'max-h-96 mt-4']) }}">
    <source src="{{ $media->url }}" type="{{ $media->content_type }}" />
</video>
