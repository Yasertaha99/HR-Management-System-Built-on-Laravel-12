<?php

namespace App\ValueObjects;

final class WorkingDuration
{
    private function __construct(
        public readonly int $totalMinutes,
        public readonly int $hours,
        public readonly int $minutes
    ) {}

    public static function fromMinutes(int $totalMinutes): self
    {
        $totalMinutes = max(0, $totalMinutes);
        $hours = (int) floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return new self($totalMinutes, $hours, $minutes);
    }

    public static function fromTimestamps(\DateTimeInterface $start, \DateTimeInterface $end): self
    {
        $diffInSeconds = max(0, $end->getTimestamp() - $start->getTimestamp());
        $totalMinutes = (int) round($diffInSeconds / 60);

        return self::fromMinutes($totalMinutes);
    }

    public function toShortString(): string
    {
        return sprintf('%dh %02dm', $this->hours, $this->minutes);
    }

    public function toLongString(): string
    {
        return sprintf('%d hours, %d minutes', $this->hours, $this->minutes);
    }
}
