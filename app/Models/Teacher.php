<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teacher extends Model
{
    use HasFactory;

    protected $primaryKey = 'teacher_id';

    protected $fillable = [
        'admin_id',
        'name',
        'code',
        'role',
        'category',
        'subject',
        'nip',
        'nuptk',
        'email',
        'phone',
        'address',
        'image',
        'expertise',
        'education',
        'bio',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'expertise' => 'array',
            'education' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }
}
