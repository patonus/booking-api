<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+0 days', '+2 years');
        $endDate = $this->faker->dateTimeBetween($startDate, '+3 years');

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,

        ];
    }
}
