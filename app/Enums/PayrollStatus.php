<?php

namespace App\Enums;

enum PayrollStatus: string
{
    case DRAFT = 'draft';
    case CALCULATING = 'calculating';
    case CALCULATED = 'calculated';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case LOCKED = 'locked';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::CALCULATING => 'Calculating',
            self::CALCULATED => 'Calculated',
            self::UNDER_REVIEW => 'Under Review',
            self::APPROVED => 'Approved',
            self::LOCKED => 'Locked',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'badge-secondary',
            self::CALCULATING => 'badge-info',
            self::CALCULATED => 'badge-primary',
            self::UNDER_REVIEW => 'badge-warning text-dark',
            self::APPROVED => 'badge-success',
            self::LOCKED => 'badge-dark',
            self::PAID => 'badge-success font-weight-bold',
            self::CANCELLED => 'badge-danger',
        };
    }

    public function isModifiable(): bool
    {
        return in_array($this, [self::DRAFT, self::CALCULATED, self::UNDER_REVIEW]);
    }
}
