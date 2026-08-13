<?php

namespace App\Exceptions\Attendance;

use Exception;

class AttendanceNotStartedException extends Exception
{
    public function __construct(string $message = "You have not checked in for today yet.")
    {
        parent::__construct($message);
    }
}
