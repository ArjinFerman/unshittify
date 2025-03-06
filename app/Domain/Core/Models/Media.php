<?php

namespace App\Domain\Core\Models;

use App\Domain\Core\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $table = 'core_media';

    protected $casts = [
        'type' => MediaType::class,
        'quality' => 'int',
        'properties' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'entry_id',
        'remote_id',
        'type',
        'url',
        'content_type',
        'quality',
        'properties',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }
}
