<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spmb extends Model
{
    use HasFactory;

    protected $table = 'spmb';

    protected $primaryKey = 'spmb_id';

    protected $fillable = [
        'admin_id',
        'academic_year',
        'status_label',
        'headline',
        'subheadline',
        'registration_fee',
        'contact_phone',
        'contact_email',
        'brochure_url',
        'brochure_file_name',
        'brochure_uploaded_at',
        'description',
        'requirements',
        'schedule',
        'waves',
        'steps',
        'faq',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'waves' => 'array',
            'steps' => 'array',
            'faq' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }
}
