<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case WORKING = 'working';
    case COMPLETED = 'completed';
    case ABSENT = 'absent';
    case INCOMPLETE = 'incomplete';
    case HOLIDAY = 'holiday';
    case LEAVE = 'leave';
    case DAY_OFF = 'day_off';

    public function label(): string
    {
        return match ($this) {
            self::WORKING => 'Working',
            self::COMPLETED => 'Completed',
            self::ABSENT => 'Absent',
            self::INCOMPLETE => 'Incomplete',
            self::HOLIDAY => 'Holiday',
            self::LEAVE => 'Leave',
            self::DAY_OFF => 'Day Off',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::WORKING => 'bg-warning text-dark border-warning',
            self::COMPLETED => 'bg-success text-white border-success',
            self::ABSENT => 'bg-danger text-white border-danger',
            self::INCOMPLETE => 'bg-secondary text-white border-secondary',
            self::HOLIDAY => 'bg-purple text-white border-purple',
            self::LEAVE => 'bg-info text-white border-info',
            self::DAY_OFF => 'bg-light text-muted border-light',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::WORKING => 'badge-warning',
            self::COMPLETED => 'badge-success',
            self::ABSENT => 'badge-danger',
            self::INCOMPLETE => 'badge-secondary',
            self::HOLIDAY => 'badge-info',
            self::LEAVE => 'badge-primary',
            self::DAY_OFF => 'badge-light',
        };
    }
}
