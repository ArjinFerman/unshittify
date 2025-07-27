<picture>
    <img src="{{ $media->url }}" alt="{{ $media->id ?? $media->media_object_id }}" {{ $attributes->merge(['class' => 'max-h-96 mt-4']) }}">
</picture>
