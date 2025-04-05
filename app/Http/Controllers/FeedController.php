<?php

namespace App\Http\Controllers;


use App\Domain\Core\Models\Entry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request): View
    {
        $entries = Entry::query()
            ->whereNotNull('feed_id')
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        return view('entries', ['entries' => $entries]);
    }
}
