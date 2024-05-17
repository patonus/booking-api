<?php

namespace Tests\Feature\Models;

use App\Models\Reservation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use WithFaker;

    #[Group('createManyForRange')]
    public function test_created_and_updated_fields_are_populated()
    {
        $startDate = $this->faker->dateTimeBetween('+0 days', '+2 years');
        $endDate = $this->faker->dateTimeBetween($startDate, '+3 years');

        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $count = 1;

        Reservation::createManyForRange($dateRange, $count);

        $this->assertDatabaseHas('reservations', [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    #[Group('createManyForRange')]
    public function test_multiple_reservations_are_created()
    {
        $startDate = $this->faker->dateTimeBetween('+0 days', '+2 years');
        $endDate = $this->faker->dateTimeBetween($startDate, '+3 years');

        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $count = 5;

        Reservation::createManyForRange($dateRange, $count);

        $this->assertCount($count, Reservation::all());
    }

    #[Group('createManyForRange')]
    public function test_correct_dates_are_stored()
    {
        $startDate = Carbon::instance($this->faker->dateTimeBetween('+0 days', '+2 years'));
        $endDate = Carbon::instance($this->faker->dateTimeBetween($startDate, '+3 years'));

        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $count = 3;

        Reservation::createManyForRange($dateRange, $count);

        $reservations = Reservation::all();
        foreach ($reservations as $reservation) {
            $this->assertEquals($startDate->format('Y-m-d'), $reservation->start_date->format('Y-m-d'));
            $this->assertEquals($endDate->format('Y-m-d'), $reservation->end_date->subDay()->format('Y-m-d'));
        }
    }
}
