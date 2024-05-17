<?php

namespace App\Models;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vacancy extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'count'];

    protected $casts = [
        'date' => 'date',
    ];

    public static function decreaseAvailability(CarbonPeriod $dateRange, int $count)
    {

        $dates = $dateRange->toArray();

        $dates = implode(',', array_map(fn ($date) => "'$date'", $dates));

        // The vacancies are updated as a single DB statement, for performance reasons, as the date range can be arbitrarily long.
        // If it were limited to some reasonable number, I would consider retrieving each record as a separate model and updating it separately,
        // as it would significantly update readibility of the code.
        DB::statement("UPDATE vacancies SET count = count - {$count} WHERE date IN ({$dates})");
    }

    public static function isAvailable(CarbonPeriod $dateRange, int $count)
    {
        $vacanciesQuery = self::whereBetween('date', [
            $dateRange->getStartDate(), $dateRange->getEndDate(),
        ])->where('count', '>=', $count);

        // We can assume that that if there is at least as many vacancies as days, then reservation can be made, because the vacancies for a given day are unique
        return $vacanciesQuery->count() >= $dateRange->count();

    }
}
