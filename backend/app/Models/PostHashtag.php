<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostHashtag extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'post_hashtags';

    protected $fillable = [
        'post_id',
        'hashtag_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function hashtag(): BelongsTo
    {
        return $this->belongsTo(Hashtag::class);
    }
}
