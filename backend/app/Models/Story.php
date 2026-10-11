<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'media_type',
        'media_url',
        'caption',
        'views_count',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(StoryView::class);
    }

    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'story_views', 'story_id', 'viewer_id')
            ->withPivot('viewed_at');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }
}
