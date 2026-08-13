<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTelegramAccount extends Model
{
    use HasFactory;

    protected $table = 'user_telegram_accounts';

    protected $fillable = [
        'user_id',
        'telegram_chat_id',
        'telegram_user_id',
        'username',
        'first_name',
        'last_name',
        'verified_at',
        'linked_at',
        'unlinked_at',
        'is_active',
        'linking_token',
        'token_expires_at',
        'metadata',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'linked_at' => 'datetime',
        'unlinked_at' => 'datetime',
        'is_active' => 'boolean',
        'token_expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null && $this->is_active;
    }
}
