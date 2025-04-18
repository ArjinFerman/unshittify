<div class="mt-0 mb-4 text-xs/relaxed relative">
    <ul>
        <li wire:click="toggleRead" class="inline-block cursor-pointer hover:text-blue-400">
            <span class="fa @if($entry->isRead()) fa-eye-slash @else fa-eye @endif mr-1"></span><span>Read</span>
        </li>
        <li wire:click="toggleStarred" class="inline-block cursor-pointer hover:text-blue-400">
            <span class="@if($entry->isStarred()) fa @else fa-regular @endif fa-star mr-1 ml-2"></span><span>Star</span>
        </li>
        <li class="inline-block cursor-pointer hover:text-blue-400">
            <a href="{{ $entry->url }}"><span class="fa fa-arrow-up-right-from-square mr-1 ml-2"></span><span>External Link</span></a>
        </li>
        <li wire:click="subscribe" class="inline-block cursor-pointer hover:text-blue-400">
            <span class="fa fa-user mr-1 ml-2"></span><span>Subscribe</span>
        </li>
    </ul>
</div>
