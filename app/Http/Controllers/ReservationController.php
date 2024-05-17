<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Vacancy;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ReservationController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Reservation::class);

        // Normally I would consider adding here and additional layer for transforming the data, probably using API Resources - https://laravel.com/docs/11.x/eloquent-resources.
        // However here the data returned is very simple and it can be argued that none of the fields need transforming, so I omitted it.

        // The `start_date` and `end_date` fields have time in the value, even though we treat them as dates, because in a production-app there would also be a need of handling check-in and check-out dates.
        // This way it can be added without introducing breaking changes to the API
        return Reservation::latest()->paginate();
    }

    public function store(StoreReservationRequest $request)
    {
        $startDate = $request->date('start_date');
        $endDate = $request->date('end_date');
        $count = $request->integer('count');
        // This check feels a bit redundant taking validation into account, however it's useful for type-safety reasons
        if (! $startDate || ! $endDate) {
            throw new InvalidArgumentException('The date range is incomplete');
        }
        // The end date is moved to the end of the previous day, because we don't want to include check out day in the calculations
        $dateRange = CarbonPeriod::create($startDate, $endDate->subDay()->endOfDay());

        if (! Vacancy::isAvailable($dateRange, $count)) {
            return response()->json(['error' => 'There are not enough vacancies for the selected period.'], 422);
        }

        DB::transaction(function () use ($dateRange, $count) {
            Reservation::createManyForRange($dateRange, $count);
            Vacancy::decreaseAvailability($dateRange, $count);
        });

        // I chose here not to include the created resource in the response because for this app it wasn't needed. In a real API this would need to be considered.
        return response()->json(['success' => true], 201);
    }
}
