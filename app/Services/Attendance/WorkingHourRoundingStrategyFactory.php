<?php

namespace App\Services\Attendance;

use App\Contracts\Attendance\WorkingHourRoundingStrategyInterface;
use App\Enums\RoundingMethod;
use App\Strategies\Attendance\HalfHourRoundingStrategy;
use App\Strategies\Attendance\NoRoundingStrategy;
use App\Strategies\Attendance\QuarterHourRoundingStrategy;
use InvalidArgumentException;

class WorkingHourRoundingStrategyFactory
{
    public static function make(string|RoundingMethod|null $method = null): WorkingHourRoundingStrategyInterface
    {
        if ($method instanceof RoundingMethod) {
            $method = $method->value;
        }

        $method = $method ?? config('attendance.rounding_method', RoundingMethod::HALF_HOUR->value);

        return match ($method) {
            'half_hour', RoundingMethod::HALF_HOUR->value => new HalfHourRoundingStrategy(),
            'quarter_hour', RoundingMethod::QUARTER_HOUR->value => new QuarterHourRoundingStrategy(),
            'none', RoundingMethod::NONE->value => new NoRoundingStrategy(),
            default => new HalfHourRoundingStrategy(),
        };
    }
}
