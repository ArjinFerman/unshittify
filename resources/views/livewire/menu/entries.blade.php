<div>
    @if ($isTop ?? false)
        @if ($loadNewestLink ?? false)
            @if (url()->full() != $loadNewestLink)
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
        @else
            <div class="content-box content-bg mt-4 cursor-pointer" wire:click="markAllAsRead">
                <div class="pt-3 sm:pt-5 w-full">
                    <span class="font-semibold text-center block text-blue-600 dark:text-sky-400 underline">
                    {{ __('Mark ALL as read') }}
                    </span>
                </div>
            </div>
        @endif
    @else
        @if ($loadMoreLink ?? false)
            <a href="{{ $loadMoreLink }}">
                <div class="content-box content-bg mt-4">
                    <div class="pt-3 sm:pt-5 w-full">
                        <span class="font-semibold text-center block text-blue-600 dark:text-sky-400 underline">
                        {{ __('Load more') }}
                        </span>
                    </div>
                </div>
            </a>
        @else
            <div class="content-box content-bg mt-4 cursor-pointer" wire:click="markPageAsRead">
                <div class="pt-3 sm:pt-5 w-full">
                    <span class="font-semibold text-center block text-blue-600 dark:text-sky-400 underline">
                    {{ __('Mark page as read') }}
                    </span>
                </div>
            </div>
        @endif
    @endif
</div>
