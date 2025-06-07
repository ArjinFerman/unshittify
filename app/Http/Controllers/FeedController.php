<?php

namespace App\Http\Controllers;


use App\Domain\Core\Enums\FeedStatus;
use App\Domain\Core\Models\Entry;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
    public function index(Request $request): View
    {
        $entries = Entry::with([
            'feed', 'feed.author', 'feed.author.avatars', 'tags', 'media'
        ])
            ->join('core_entry_references', 'core_entry_references.ref_entry_id', '=', 'core_entries.id', 'left')
            ->whereHas('feed', function ($query) {
                $query->whereStatus(FeedStatus::ACTIVE);
            })
            ->orWhereExists(function (Builder $query) {
                $query->from('core_entries', 'ce_ref')
                    ->where('core_entry_references.ref_path', 'LIKE', DB::raw("CONCAT('%/', ce_ref.id, '/%')"))
                    ->whereExists(function (Builder $feedQuery) {
                        $feedQuery->from('core_feeds', 'cf')
                            ->where('cf.id', '=', DB::raw('ce_ref.feed_id'))
                            ->whereStatus(FeedStatus::ACTIVE);
                    });
            })
            ->orderBy('published_at', 'desc')
            ->limit(400)
            ->get()
            ->optimizeReferences()
        ;

        return view('entries', ['entries' => $entries]);
    }
}
