<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hashtag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'posts_count',
    ];

    protected function casts(): array
    {
        return [
            'posts_count' => 'integer',
        ];
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_hashtags')
            ->withPivot('created_at');
    }

    public function postHashtags(): HasMany
    {
        return $this->hasMany(PostHashtag::class);
    }
}
