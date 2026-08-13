<?php

namespace App\Models;

use App\Enums\PayrollStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

    protected $fillable = [
        'payroll_period_id',
        'user_id',
        'status',
        'gross_pay_minor',
        'total_deductions_minor',
        'net_pay_minor',
        'currency',
    ];

    protected $casts = [
        'status' => PayrollStatus::class,
        'gross_pay_minor' => 'integer',
        'total_deductions_minor' => 'integer',
        'net_pay_minor' => 'integer',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(PayrollSnapshot::class);
    }

    public function getFormattedGrossPayAttribute(): string
    {
        return sprintf('%.2f %s', $this->gross_pay_minor / 100, $this->currency);
    }

    public function getFormattedTotalDeductionsAttribute(): string
    {
        return sprintf('%.2f %s', $this->total_deductions_minor / 100, $this->currency);
    }

    public function getFormattedNetPayAttribute(): string
    {
        return sprintf('%.2f %s', $this->net_pay_minor / 100, $this->currency);
    }
}
