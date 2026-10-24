<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'admin_id';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function schoolProfile(): HasOne
    {
        return $this->hasOne(SchoolProfile::class, 'admin_id', 'admin_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'admin_id', 'admin_id');
    }

    public function extracurriculars(): HasMany
    {
        return $this->hasMany(Extracurricular::class, 'admin_id', 'admin_id');
    }

    public function spmb(): HasMany
    {
        return $this->hasMany(Spmb::class, 'admin_id', 'admin_id');
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'admin_id', 'admin_id');
    }

    public function knowledgeBases(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class, 'admin_id', 'admin_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'admin_id', 'admin_id');
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class, 'admin_id', 'admin_id');
    }
}
