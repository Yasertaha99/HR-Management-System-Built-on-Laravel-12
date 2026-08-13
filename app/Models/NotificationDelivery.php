<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    use HasFactory;

    protected $table = 'notification_deliveries';

    protected $fillable = [
        'notification_id',
        'channel',
        'status',
        'provider_message_id',
        'attempts',
        'last_attempt_at',
        'sent_at',
        'failed_at',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'status' => DeliveryStatus::class,
        'attempts' => 'integer',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(NotificationRecord::class, 'notification_id');
    }
}
