@php
    /** @var \App\Domain\Core\DTO\MediaDTO $media */
@endphp
<picture>
    <img src="{{ $media->url }}" alt="{{ $media->composite_id }}" {{ $attributes->merge(['class' => 'max-h-96 mt-4']) }}">
</picture>
