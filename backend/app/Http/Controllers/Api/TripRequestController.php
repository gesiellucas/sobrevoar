<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequestRequest;
use App\Http\Requests\UpdateTripRequestStatusRequest;
use App\Http\Resources\TripRequestResource;
use App\Models\TripRequest;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class TripRequestController extends Controller
{
    /**
     * Display a listing of trip requests.
     */
    public function index(Request $request)
    {
        $query = TripRequest::with(['traveler.user', 'destination']);

        // If not admin, only show own requests (through traveler)
        if (!$request->user()->is_admin) {
            $query->byUser($request->user()->id);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->status($request->status);
        }

        if ($request->has('destination_id')) {
            $query->byDestination($request->destination_id);
        }

        if ($request->has('traveler_id')) {
            $query->byTraveler($request->traveler_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $tripRequests = $query->orderBy('departure_datetime', 'desc')->paginate($perPage);

        return TripRequestResource::collection($tripRequests);
    }

    /**
     * Store a newly created trip request.
     */
    public function store(StoreTripRequestRequest $request)
    {
        $travelerId = null;

        // Admin can create trip for any traveler
        if ($request->user()->is_admin && $request->filled('traveler_id')) {
            $travelerId = $request->traveler_id;
        } else {
            // Get traveler for current user
            $traveler = $request->user()->travelers()->where('is_active', true)->first();

            if (!$traveler) {
                return response()->json([
                    'message' => 'User does not have an active traveler profile.',
                ], 422);
            }

            $travelerId = $traveler->id;
        }

        $tripRequest = TripRequest::create([
            'traveler_id' => $travelerId,
            'destination_id' => $request->destination_id,
            'description' => $request->description,
            'departure_datetime' => $request->departure_datetime,
            'return_datetime' => $request->return_datetime,
            'status' => 'requested',
        ]);

        return new TripRequestResource($tripRequest->load(['traveler.user', 'destination']));
    }

    /**
     * Display the specified trip request.
     */
    public function show(Request $request, TripRequest $tripRequest)
    {
        // Check authorization
        $isOwner = $tripRequest->traveler->user_id === $request->user()->id;

        if (!$request->user()->is_admin && !$isOwner) {
            abort(403, 'Unauthorized action.');
        }

        return new TripRequestResource($tripRequest->load(['traveler.user', 'destination']));
    }

    /**
     * Update the specified trip request.
     */
    public function update(Request $request, TripRequest $tripRequest)
    {
        // Only the owner can update
        if ($tripRequest->traveler->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Can only update if status is 'requested'
        if ($tripRequest->status !== 'requested') {
            abort(422, 'Cannot update trip request with current status.');
        }

        $validated = $request->validate([
            'destination_id' => 'sometimes|exists:destinations,id',
            'description' => 'sometimes|nullable|string',
            'departure_datetime' => 'sometimes|date|after:now',
            'return_datetime' => 'sometimes|date|after:departure_datetime',
        ]);

        $tripRequest->update($validated);

        return new TripRequestResource($tripRequest->load(['traveler.user', 'destination']));
    }

    /**
     * Remove the specified trip request (cancel).
     */
    public function destroy(Request $request, TripRequest $tripRequest)
    {
        // Only the owner can cancel
        if ($tripRequest->traveler->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Can only cancel if status is 'requested'
        if ($tripRequest->status !== 'requested') {
            abort(422, 'Cannot cancel trip request with current status.');
        }

        $tripRequest->delete();

        return response()->json([
            'message' => 'Trip request cancelled successfully',
        ]);
    }

    /**
     * Update the status of a trip request (admin only).
     */
    public function updateStatus(UpdateTripRequestStatusRequest $request, TripRequest $tripRequest)
    {
        $oldStatus = $tripRequest->status;
        $newStatus = $request->status;

        $tripRequest->update([
            'status' => $newStatus,
        ]);

        // Create notification for the user
        if ($oldStatus !== $newStatus) {
            $user = $tripRequest->traveler->user;
            $destination = $tripRequest->destination->full_location;

            $statusMessages = [
                'approved' => "Sua solicitação de viagem para {$destination} foi aprovada.",
                'cancelled' => "Sua solicitação de viagem para {$destination} foi cancelada.",
            ];

            if (isset($statusMessages[$newStatus])) {
                UserNotification::create([
                    'user_id' => $user->id,
                    'message' => $statusMessages[$newStatus],
                    'is_checked' => false,
                ]);
            }
        }

        return new TripRequestResource($tripRequest->load(['traveler.user', 'destination']));
    }
}
