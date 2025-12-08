@php
    /** @var \App\Domain\Core\DTO\EntryDTO $entry */
@endphp
<div class="mt-0 mb-4 text-xs/relaxed relative">
    <ul>
        <li wire:click="toggleRead" class="inline-block cursor-pointer hover:text-blue-400">
            <span class="fa @if($entry->is_read) fa-eye-slash @else fa-eye @endif mr-1"></span><span>{{ __('Read') }}</span>
        </li>
        <li wire:click="toggleStarred" class="inline-block cursor-pointer hover:text-blue-400">
            <span class="@if($entry->is_starred) fa @else fa-regular @endif fa-star mr-1 ml-2"></span><span>{{ __('Star') }}</span>
        </li>
        <li class="inline-block cursor-pointer hover:text-blue-400">
            <a href="{{ $entry->url }}"><span class="fa fa-arrow-up-right-from-square mr-1 ml-2"></span><span>{{ __('External Link') }}</span></a>
        </li>
        <li wire:click="subscribe" class="inline-block cursor-pointer hover:text-blue-400">
            @if($entry->displayEntry()->feed->status == \App\Domain\Core\Enums\FeedStatus::ACTIVE)
            <span class="fa fa-user-check mr-1 ml-2"></span><span>{{ __('Subscribed')}}</span>
            @else
            <span class="fa fa-user mr-1 ml-2"></span><span>{{ __('Subscribe')}}</span>
            @endif
        </li>
        <li class="inline-block cursor-pointer hover:text-blue-400">
            <a href="{{ route('twitter.tweet', ['screenName' => $entry->feed?->name, 'tweetId' => $entry->composite_id->externalId]) }}">
                <span class="fa fa-wifi mr-1 ml-2"></span><span>{{ __('API View') }}</span>
            </a>
        </li>
    </ul>
</div>
