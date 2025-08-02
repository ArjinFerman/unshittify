<div class="mt-0 mb-4 text-xs/relaxed relative @if(!$entry) bg-gray-50/25 @endif">
    <ul>
        <li wire:click="toggleRead" class="inline-block cursor-pointer hover:text-blue-400">
            <span class="fa @if($entry?->isRead()) fa-eye-slash @else fa-eye @endif mr-1"></span><span>{{ __('Read') }}</span>
        </li>
        <li wire:click="toggleStarred" class="inline-block cursor-pointer hover:text-blue-400">
            <span class="@if($entry?->isStarred()) fa @else fa-regular @endif fa-star mr-1 ml-2"></span><span>{{ __('Star') }}</span>
        </li>
        <li class="inline-block cursor-pointer hover:text-blue-400">
            <a href="{{ $url }}"><span class="fa fa-arrow-up-right-from-square mr-1 ml-2"></span><span>{{ __('External Link') }}</span></a>
        </li>
        <li wire:click="subscribe" class="inline-block cursor-pointer hover:text-blue-400">
            @if($entry?->feed?->status == \App\Domain\Core\Enums\FeedStatus::ACTIVE)
            <span class="fa fa-user-check mr-1 ml-2"></span><span>{{ __('Subscribed')}}</span>
            @else
            <span class="fa fa-user mr-1 ml-2"></span><span>{{ __('Subscribe')}}</span>
            @endif
        </li>
    </ul>
</div>
