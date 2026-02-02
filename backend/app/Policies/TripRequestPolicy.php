<?php

namespace App\Policies;

use App\Models\TripRequest;
use App\Models\User;

class TripRequestPolicy
{
    /**
     * Determine if the user can view any trip requests.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view list
    }

    /**
     * Determine if the user can view the trip request.
     */
    public function view(User $user, TripRequest $tripRequest): bool
    {
        // Admins can view all, users can only view their own
        return $user->is_admin || $tripRequest->user_id === $user->id;
    }

    /**
     * Determine if the user can create trip requests.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create
    }

    /**
     * Determine if the user can update the trip request.
     */
    public function update(User $user, TripRequest $tripRequest): bool
    {
        // Only the owner can update, and only if status is 'requested'
        return $tripRequest->user_id === $user->id && $tripRequest->status === 'requested';
    }

    /**
     * Determine if the user can delete the trip request.
     */
    public function delete(User $user, TripRequest $tripRequest): bool
    {
        // Only the owner can delete, and only if status is 'requested'
        return $tripRequest->user_id === $user->id && $tripRequest->status === 'requested';
    }

    /**
     * Determine if the user can update the status of the trip request.
     */
    public function updateStatus(User $user, TripRequest $tripRequest): bool
    {
        // Only admins can update status
        return $user->is_admin;
    }
}
