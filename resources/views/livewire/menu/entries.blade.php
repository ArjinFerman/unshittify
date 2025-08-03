<div>
    @if ($showMarkAll)
        <div class="content-box content-bg mt-4 cursor-pointer" wire:click="markAllAsRead" >
            <div class="pt-3 sm:pt-5 w-full">
            <span class="font-semibold text-center block text-blue-600 dark:text-sky-400 underline">
            {{ __('Mark ALL as read') }}
            </span>
            </div>
        </div>
    @endif
    @if ($showMarkPage)
    <div class="content-box content-bg mt-4 cursor-pointer" wire:click="markPageAsRead" >
        <div class="pt-3 sm:pt-5 w-full">
            <span class="font-semibold text-center block text-blue-600 dark:text-sky-400 underline">
            {{ __('Mark page as read') }}
            </span>
        </div>
    </div>
    @endif
</div>
