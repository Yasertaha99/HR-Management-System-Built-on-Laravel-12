<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\ValueObjects\WorkingDuration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'attendance_date',
        'check_in',
        'check_out',
        'status',
        'total_minutes',
        'rounded_hours',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date:Y-m-d',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'status' => AttendanceStatus::class,
        'total_minutes' => 'integer',
        'rounded_hours' => 'integer',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'overtime_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isWorking(): bool
    {
        return $this->status === AttendanceStatus::WORKING;
    }

    public function isCompleted(): bool
    {
        return $this->status === AttendanceStatus::COMPLETED;
    }

    public function hasCheckedIn(): bool
    {
        return $this->check_in !== null;
    }

    public function hasCheckedOut(): bool
    {
        return $this->check_out !== null;
    }

    public function getFormattedDurationAttribute(): string
    {
        if ($this->total_minutes === null) {
            if ($this->isWorking() && $this->check_in) {
                $elapsedMinutes = (int) max(0, $this->check_in->diffInMinutes(now()));
                return WorkingDuration::fromMinutes($elapsedMinutes)->toShortString();
            }
            return '—';
        }

        return WorkingDuration::fromMinutes($this->total_minutes)->toShortString();
    }
}
