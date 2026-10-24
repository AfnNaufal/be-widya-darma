<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gallery extends Model
{
    use HasFactory;

    protected $primaryKey = 'gallery_id';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'news_id',
        'title',
        'image_url',
        'caption',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class, 'news_id', 'news_id');
    }
}
