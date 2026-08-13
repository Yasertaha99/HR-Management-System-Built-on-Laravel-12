<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationRecord extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'recipient_id',
        'type',
        'priority',
        'title',
        'body',
        'action_url',
        'data',
        'read_at',
    ];

    protected $casts = [
        'type' => NotificationType::class,
        'priority' => NotificationPriority::class,
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class, 'notification_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
