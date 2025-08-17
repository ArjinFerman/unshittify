<?php

namespace App\Http\Controllers;


use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Services\EntryService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntryController extends Controller
{
    public function __construct(protected EntryService $entryService)
    {

    }

    public function index(Request $request): View
    {
        $data = [
            'entries'       => $this->entryService->getSubscribedFeedEntriesUnread(),
            'unreadCount'   => $this->entryService->getUnreadCount(),
        ];

        $data['title'] = __('core.unread.title', ['unread_count' => $data['unreadCount']]);

        return view('entries', $data);
    }

    public function starred(Request $request): View
    {
        $data = [
            'entries'       => $this->entryService->getStarredEntries(),
            'unreadCount'   => $this->entryService->getUnreadCount(),
            'title'         => __('core.starred.title'),
        ];

        return view('entries', $data);
    }
}
