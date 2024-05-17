<?php

namespace Tests\Feature\Models;

use App\Models\Vacancy;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class VacancyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Vacancy::create(['date' => Carbon::today(), 'count' => 10]);
        Vacancy::create(['date' => Carbon::tomorrow(), 'count' => 5]);
        Vacancy::create(['date' => Carbon::today()->addDays(2), 'count' => 20]);
    }

    #[Group('decreaseAvailability')]
    public function test_decreases_availability_for_all_vacancies_in_range()
    {
        $dateRange = CarbonPeriod::create(Carbon::today(), Carbon::tomorrow());
        Vacancy::decreaseAvailability($dateRange, 2);

        $this->assertDatabaseHas('vacancies', ['date' => Carbon::today()->toDateString(), 'count' => 8]);
        $this->assertDatabaseHas('vacancies', ['date' => Carbon::tomorrow()->toDateString(), 'count' => 3]);
    }

    #[Group('decreaseAvailability')]
    public function test_decreases_availability_for_one_vacancy()
    {
        $singleDayRange = CarbonPeriod::create(Carbon::today(), Carbon::today());
        Vacancy::decreaseAvailability($singleDayRange, 5);

        $this->assertDatabaseHas('vacancies', ['date' => Carbon::today()->toDateString(), 'count' => 5]);
    }

    #[Group('isAvailable')]
    public function test_returns_true_if_there_are_vacancies_in_date_range()
    {
        $dateRange = CarbonPeriod::create(Carbon::today(), Carbon::tomorrow());

        $this->assertTrue(Vacancy::isAvailable($dateRange, 5));
    }

    #[Group('isAvailable')]
    public function test_returns_false_when_vacancies_count_is_too_low()
    {
        $dateRange = CarbonPeriod::create(Carbon::today(), Carbon::tomorrow());

        $this->assertFalse(Vacancy::isAvailable($dateRange, 6));
    }

    #[Group('isAvailable')]
    public function test_returns_false_when_there_is_missing_vacancy()
    {
        $dateRange = CarbonPeriod::create(Carbon::today(), Carbon::today()->addDays(3));

        $this->assertFalse(Vacancy::isAvailable($dateRange, 1));
    }
}
