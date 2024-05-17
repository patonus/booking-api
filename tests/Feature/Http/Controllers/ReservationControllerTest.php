<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Reservation;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class ReservationControllerTest extends TestCase
{
    #[Group('reservations.index')]
    public function test_user_is_authorized_to_view_reservations()
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('viewAny', Reservation::class)
            ->andReturn(true);

        $response = $this->getJson(route('reservations.index'));

        $response->assertStatus(200);
    }

    #[Group('reservations.index')]
    public function test_resources_are_paginated()
    {
        Reservation::factory()->count(30)->create();

        $response = $this->getJson(route('reservations.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'start_date',
                        'end_date',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links' => [
                    '*' => [
                        'url',
                        'label',
                        'active',
                    ],
                ],
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]);
        $this->assertCount(15, $response->json('data'));
    }

    #[Group('reservations.store')]
    public function test_error_is_thrown_when_end_date_is_earlier_than_start_date()
    {
        $data = [
            'start_date' => now()->addMonth()->toIso8601String(),
            'end_date' => now()->addWeek()->toIso8601String(),
            'count' => 3,
        ];

        $response = $this->postJson(route('reservations.store'), $data);

        $response->assertStatus(422);
        $response->assertInvalid('end_date');

    }

    #[Group('reservations.store')]
    public function test_error_is_thrown_when_start_date_is_in_the_past()
    {
        $data = [
            'start_date' => now()->subMonth()->toIso8601String(),
            'end_date' => now()->addWeek()->toIso8601String(),
            'count' => 3,
        ];

        $response = $this->postJson(route('reservations.store'), $data);

        $response->assertStatus(422);
        $response->assertInvalid('start_date');

    }

    #[Group('reservations.store')]
    public function test_error_is_thrown_when_count_is_smaller_than_1()
    {
        $data = [
            'start_date' => now()->addWeek()->toIso8601String(),
            'end_date' => now()->addWeeks(2)->toIso8601String(),
            'count' => 0,
        ];

        $response = $this->postJson(route('reservations.store'), $data);

        $response->assertStatus(422);
        $response->assertInvalid('count');
    }

    #[Group('reservations.store')]
    public function test_missing_vacancies_are_handled()
    {
        Vacancy::factory()->create(['date' => now()->startOfDay(), 'count' => 1]);
        $data = [
            'start_date' => now()->toDateString(),
            'end_date' => now()->tomorrow()->toDateString(),
            'count' => 2,
        ];

        $response = $this->postJson(route('reservations.store'), $data);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('reservations', [
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);
    }

    #[Group('reservations.store')]
    public function test_all_reservations_are_created()
    {
        $vacancy = Vacancy::factory()->create(['date' => now()->startOfDay(), 'count' => 2]);
        $data = [
            'start_date' => now()->toDateString(),
            'end_date' => now()->tomorrow()->toDateString(),
            'count' => 2,
        ];

        $response = $this->postJson(route('reservations.store'), $data);

        $response->assertCreated();
        $this->assertCount(2, Reservation::where('start_date', $data['start_date'])
            ->where('end_date', $data['end_date'])
            ->get()
        );
        $vacancy = $vacancy->refresh();
        $this->assertSame(0, $vacancy->count);
    }

    #[Group('reservations.store')]
    public function test_all_selected_vacancies_has_decreased_count()
    {
        $vacancy1 = Vacancy::factory()->create(['date' => now()->startOfDay(), 'count' => 1]);
        $vacancy2 = Vacancy::factory()->create(['date' => now()->addDay()->startOfDay(), 'count' => 1]);

        $data = [
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'count' => 1,
        ];

        $response = $this->postJson(route('reservations.store'), $data);
        $response->assertCreated();
        $this->assertSame(0, $vacancy1->refresh()->count);
        $this->assertSame(0, $vacancy2->refresh()->count);

    }

    #[Group('reservations.store')]
    public function test_single_day_reservations_are_handled()
    {
        Vacancy::factory()->create(['date' => now()->startOfDay(), 'count' => 1]);
        $data = [
            'start_date' => now()->toDateString(),
            'end_date' => now()->tomorrow()->toDateString(),
            'count' => 1,
        ];

        $response = $this->postJson(route('reservations.store'), $data);
        $response->assertCreated();
        $this->assertDatabaseHas('reservations', [
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);
    }
}
