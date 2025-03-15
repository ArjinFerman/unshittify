<?php

namespace App\Domain\Web\Models;

use App\Domain\Core\Models\Entryable;

class Page extends Entryable
{
    protected $table = 'web_pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_content',
    ];

    public function getEntryType(): string
    {
        return 'link';
    }
}
