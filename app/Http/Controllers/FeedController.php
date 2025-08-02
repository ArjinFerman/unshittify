<?php

namespace App\Http\Controllers;


use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Entry;
use App\Domain\Core\Services\FeedService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
    public function __construct(protected FeedService $feedService)
    {

    }

    public function index(Request $request): View
    {
        $entries = $this->feedService->getSubscribedFeedEntriesUnread();

        return view('entries', ['entries' => $entries]);
    }
}
