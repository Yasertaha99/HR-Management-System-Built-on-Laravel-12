<?php

namespace App\Models;

use App\Enums\CompensationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCompensation extends Model
{
    use HasFactory;

    protected $table = 'employee_compensations';

    protected $fillable = [
        'user_id',
        'effective_from',
        'effective_to',
        'compensation_type',
        'base_salary_minor',
        'hourly_rate_minor',
        'daily_rate_minor',
        'overtime_multiplier',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date:Y-m-d',
        'effective_to' => 'date:Y-m-d',
        'compensation_type' => CompensationType::class,
        'base_salary_minor' => 'integer',
        'hourly_rate_minor' => 'integer',
        'daily_rate_minor' => 'integer',
        'overtime_multiplier' => 'float',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
