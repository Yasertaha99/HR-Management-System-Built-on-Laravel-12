<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    use HasFactory;

    protected $table = 'journal_entries';

    protected $fillable = [
        'payroll_journal_id',
        'account_type',
        'account_name',
        'entry_type',
        'amount_minor',
        'currency',
        'description',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(PayrollJournal::class, 'payroll_journal_id');
    }
}
