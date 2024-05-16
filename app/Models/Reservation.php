<?php

namespace App\Models;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['start_date', 'end_date'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public static function createManyForRange(CarbonPeriod $dateRange, int $count)
    {
        // There are two ways to go about creating multiple reservations. Either create each reservation as a separate model - the way I made it,
        // or add another field to the `Reservation` model that would keep the number of reservations.
        // Which option is correct is app-specific, for this toy app it does not make much difference.
        $reservationsData = array_fill(0, $count, [
            'start_date' => $dateRange->getStartDate(),
            'end_date' => $dateRange->getEndDate('ceil'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return self::insert($reservationsData);

    }
}
