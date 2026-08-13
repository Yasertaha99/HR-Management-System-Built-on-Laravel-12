<?php

namespace App\Exceptions\Attendance;

use Exception;

class AttendanceAlreadyStartedException extends Exception
{
    public function __construct(string $message = "You have already started your workday for today.")
    {
        parent::__construct($message);
    }
}
