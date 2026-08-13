<?php

namespace App\DTOs\Attendance;

use Carbon\CarbonInterface;

final class CheckOutData
{
    public function __construct(
        public readonly int $userId,
        public readonly CarbonInterface $timestamp,
        public readonly ?string $notes = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null
    ) {}
}
