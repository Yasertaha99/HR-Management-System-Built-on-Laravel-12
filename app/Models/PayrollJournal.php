<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollJournal extends Model
{
    use HasFactory;

    protected $table = 'payroll_journals';

    protected $fillable = [
        'payroll_period_id',
        'reference_number',
        'posting_date',
        'status',
        'total_debits_minor',
        'total_credits_minor',
        'currency',
    ];

    protected $casts = [
        'posting_date' => 'date:Y-m-d',
        'total_debits_minor' => 'integer',
        'total_credits_minor' => 'integer',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'payroll_journal_id');
    }

    public function isBalanced(): bool
    {
        return $this->total_debits_minor === $this->total_credits_minor;
    }
}
