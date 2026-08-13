<?php

namespace App\Enums;

enum NotificationType: string
{
    case ATTENDANCE_STARTED = 'attendance_started';
    case ATTENDANCE_COMPLETED = 'attendance_completed';
    case MISSING_CHECKOUT = 'missing_checkout';
    case LATE_ARRIVAL = 'late_arrival';
    case EARLY_LEAVE = 'early_leave';
    case OVERTIME = 'overtime';

    case LEAVE_SUBMITTED = 'leave_submitted';
    case LEAVE_APPROVED = 'leave_approved';
    case LEAVE_REJECTED = 'leave_rejected';

    case CORRECTION_SUBMITTED = 'correction_submitted';
    case CORRECTION_APPROVED = 'correction_approved';
    case CORRECTION_REJECTED = 'correction_rejected';

    case PAYROLL_CALCULATED = 'payroll_calculated';
    case PAYROLL_APPROVED = 'payroll_approved';
    case PAYROLL_PAID = 'payroll_paid';
    case PAYROLL_LOCKED = 'payroll_locked';

    case SYSTEM_ALERT = 'system_alert';

    public function label(): string
    {
        return match ($this) {
            self::ATTENDANCE_STARTED => 'Workday Started',
            self::ATTENDANCE_COMPLETED => 'Workday Completed',
            self::MISSING_CHECKOUT => 'Missing Checkout Alert',
            self::LATE_ARRIVAL => 'Late Arrival Alert',
            self::EARLY_LEAVE => 'Early Leave Alert',
            self::OVERTIME => 'Overtime Recorded',
            self::LEAVE_SUBMITTED => 'Leave Request Submitted',
            self::LEAVE_APPROVED => 'Leave Request Approved',
            self::LEAVE_REJECTED => 'Leave Request Rejected',
            self::CORRECTION_SUBMITTED => 'Attendance Correction Submitted',
            self::CORRECTION_APPROVED => 'Attendance Correction Approved',
            self::CORRECTION_REJECTED => 'Attendance Correction Rejected',
            self::PAYROLL_CALCULATED => 'Payroll Calculated',
            self::PAYROLL_APPROVED => 'Payroll Approved',
            self::PAYROLL_PAID => 'Payslip Available',
            self::PAYROLL_LOCKED => 'Payroll Locked',
            self::SYSTEM_ALERT => 'System Alert',
        };
    }
}
