<?php

namespace App\Exceptions\Attendance;

use Exception;

class AttendanceAlreadyCompletedException extends Exception
{
    public function __construct(string $message = "You have already completed your workday for today.")
    {
        parent::__construct($message);
    }
}
