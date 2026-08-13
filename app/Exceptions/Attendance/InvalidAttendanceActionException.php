<?php

namespace App\Exceptions\Attendance;

use Exception;

class InvalidAttendanceActionException extends Exception
{
    public function __construct(string $message = "Invalid attendance state transition requested.")
    {
        parent::__construct($message);
    }
}
