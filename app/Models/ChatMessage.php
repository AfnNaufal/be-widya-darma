<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $primaryKey = 'message_id';

    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'kb_id',
        'sender',
        'message',
        'intent',
        'is_answered',
        'tokens_used',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_answered' => 'boolean',
            'tokens_used' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'session_id', 'session_id');
    }

    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class, 'kb_id', 'kb_id');
    }
}
