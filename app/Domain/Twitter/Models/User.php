<?php

namespace App\Domain\Twitter\Models;

use App\Domain\Core\Models\Author;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $table = 'twitter_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'author_id',
        'screen_name',
        'twitter_user_id',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
