<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class News extends Model
{
    use HasFactory;

    protected $primaryKey = 'news_id';

    protected $fillable = [
        'admin_id',
        'title',
        'slug',
        'content',
        'image_url',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'news_id', 'news_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(PageVisit::class, 'news_id', 'news_id');
    }
}
