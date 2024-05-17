<?php

namespace Database\Seeders;

use App\Models\Vacancy;
use Illuminate\Database\Seeder;
use Illuminate\Database\UniqueConstraintViolationException;

class VacancySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dates = [now(), now()->addDay(), now()->addDays(2)];
        foreach ($dates as $date) {
            if (! Vacancy::whereDate('date', $date)->exists()) {
                Vacancy::factory()->create(['date' => $date]);

            }
        }
        try {
            Vacancy::factory()->count(10)->create();
        } catch (UniqueConstraintViolationException) {
            // Ignoring this error is not a perfect solution,
            // but it allows running this seeder multiple times without throwing errors
        }

    }
}
