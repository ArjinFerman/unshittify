<?php

namespace App\Domain\Legacy\V1\DTO;

use App\Domain\Core\DTO\EntryDTO;
use App\Domain\Core\Enums\ExternalSourceType;
use App\Domain\Core\Traits\DTO\HasMetadata;
use App\Support\CompositeId;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use stdClass;

class LegacyEntryDTO extends EntryDTO
{
    use HasMetadata;


}
