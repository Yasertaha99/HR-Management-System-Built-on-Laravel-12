<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkScheduleDay extends Model
{
    use HasFactory;

    protected $table = 'work_schedule_days';

    protected $fillable = [
        'work_schedule_id',
        'day_of_week',
        'is_working_day',
        'start_time',
        'end_time',
        'expected_minutes',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_working_day' => 'boolean',
        'expected_minutes' => 'integer',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }
}
