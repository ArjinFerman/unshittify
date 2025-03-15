<?php

namespace App\Domain\Web\Jobs;

use App\Domain\Web\Actions\ImportWebPageAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportWebPageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $url,
    ) {}

    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        (new ImportWebPageAction())->execute($this->url);
    }
}
