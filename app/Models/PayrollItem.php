<?php

namespace App\Models;

use App\Enums\PayrollItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    use HasFactory;

    protected $table = 'payroll_items';

    protected $fillable = [
        'payroll_id',
        'type',
        'code',
        'description',
        'quantity',
        'unit_amount_minor',
        'amount_minor',
        'metadata',
    ];

    protected $casts = [
        'type' => PayrollItemType::class,
        'quantity' => 'float',
        'unit_amount_minor' => 'integer',
        'amount_minor' => 'integer',
        'metadata' => 'array',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
