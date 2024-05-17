<?php

namespace App\Policies;

use App\Models\User;

// Allowed for all (even without log-in) for simpler testing.
class ReservationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // In real life I would keep the authorization logic here simple (for example only logged-in, some other app-specific logic),
        // but in business logic define the rules which reservations a given user can see - user can see only own, place owner can see only for his place, admin can see all
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // In real life the authorization logic would be highly app-specific (maybe only for verified users, not banned by a given place and so on).

        return true;
    }
}
