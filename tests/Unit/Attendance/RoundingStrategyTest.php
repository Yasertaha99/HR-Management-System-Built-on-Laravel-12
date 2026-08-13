<?php

namespace Tests\Unit\Attendance;

use App\Strategies\Attendance\HalfHourRoundingStrategy;
use App\Strategies\Attendance\NoRoundingStrategy;
use App\Strategies\Attendance\QuarterHourRoundingStrategy;
use PHPUnit\Framework\TestCase;

class RoundingStrategyTest extends TestCase
{
    /**
     * Test HalfHourRoundingStrategy boundary values specified in requirements:
     * 0-29 additional minutes -> do not add an hour
     * 30-59 additional minutes -> add one hour
     */
    public function test_half_hour_rounding_strategy_exact_boundaries(): void
    {
        $strategy = new HalfHourRoundingStrategy();

        // 0-29 minute boundary tests
        $this->assertEquals(0, $strategy->round(0));
        $this->assertEquals(0, $strategy->round(29));
        $this->assertEquals(1, $strategy->round(30));
        $this->assertEquals(1, $strategy->round(31));

        // 8 hour base tests (480 minutes = 8h 00m)
        $this->assertEquals(8, $strategy->round(480)); // 480m -> 8h
        $this->assertEquals(8, $strategy->round(481)); // 481m -> 8h
        $this->assertEquals(8, $strategy->round(509)); // 509m (8h 29m) -> 8h

        // 30+ minute threshold (510 minutes = 8h 30m)
        $this->assertEquals(9, $strategy->round(510)); // 510m (8h 30m) -> 9h
        $this->assertEquals(9, $strategy->round(511)); // 511m (8h 31m) -> 9h
        $this->assertEquals(9, $strategy->round(539)); // 539m (8h 59m) -> 9h

        // 9 hour base tests (540 minutes = 9h 00m)
        $this->assertEquals(9, $strategy->round(540)); // 540m -> 9h
        $this->assertEquals(9, $strategy->round(569)); // 569m (9h 29m) -> 9h

        // 30+ minute threshold (570 minutes = 9h 30m)
        $this->assertEquals(10, $strategy->round(570)); // 570m (9h 30m) -> 10h
    }

    public function test_no_rounding_strategy(): void
    {
        $strategy = new NoRoundingStrategy();

        $this->assertEquals(8, $strategy->round(480));
        $this->assertEquals(8, $strategy->round(509));
        $this->assertEquals(8, $strategy->round(510));
        $this->assertEquals(8, $strategy->round(539));
        $this->assertEquals(9, $strategy->round(540));
    }

    public function test_quarter_hour_rounding_strategy(): void
    {
        $strategy = new QuarterHourRoundingStrategy();

        $this->assertEquals(8, $strategy->round(480)); // 8h
        $this->assertEquals(8, $strategy->round(487)); // 8h 07m -> 8h
    }
}
