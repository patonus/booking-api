<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Vacancy;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReservationController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Reservation::class);

        return Reservation::latest()->paginate(25); //TODO: Add resource
    }

    public function store(StoreReservationRequest $request)
    {
        $startDate = $request->date('start_date');
        $endDate = $request->date('end_date');
        $count = $request->integer('count');
        // This check feels a bit redundant taking validation into account, however it's useful for type-safety reasons
        if (! $startDate || ! $endDate) {
            throw new Exception('The date range is incomplete'); // TODO: Rewrite to a custom exception
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

        return response()->json([], 201);
    }
}
