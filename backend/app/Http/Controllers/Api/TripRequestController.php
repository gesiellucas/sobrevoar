<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequestRequest;
use App\Http\Requests\UpdateTripRequestStatusRequest;
use App\Http\Resources\TripRequestResource;
use App\Models\TripRequest;
use App\Notifications\TripRequestStatusChanged;
use Illuminate\Http\Request;

class TripRequestController extends Controller
{
    /**
     * Display a listing of trip requests.
     */
    public function index(Request $request)
    {
        $query = TripRequest::with('user');

        // If not admin, only show own requests
        if (!$request->user()->is_admin) {
            $query->where('user_id', $request->user()->id);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->status($request->status);
        }

        if ($request->has('destination')) {
            $query->destination($request->destination);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $tripRequests = $query->latest()->paginate($perPage);

        return TripRequestResource::collection($tripRequests);
    }

    /**
     * Store a newly created trip request.
     */
    public function store(StoreTripRequestRequest $request)
    {
        $tripRequest = TripRequest::create([
            'user_id' => $request->user()->id,
            'requester_name' => $request->requester_name,
            'destination' => $request->destination,
            'departure_date' => $request->departure_date,
            'return_date' => $request->return_date,
            'status' => 'requested',
        ]);

        return new TripRequestResource($tripRequest->load('user'));
    }

    /**
     * Display the specified trip request.
     */
    public function show(Request $request, TripRequest $tripRequest)
    {
        // Check authorization
        if (!$request->user()->is_admin && $tripRequest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        return new TripRequestResource($tripRequest->load('user'));
    }

    /**
     * Update the specified trip request.
     */
    public function update(Request $request, TripRequest $tripRequest)
    {
        // Only the owner can update
        if ($tripRequest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Can only update if status is 'requested'
        if ($tripRequest->status !== 'requested') {
            abort(422, 'Cannot update trip request with current status.');
        }

        $validated = $request->validate([
            'requester_name' => 'sometimes|string|max:255',
            'destination' => 'sometimes|string|max:255',
            'departure_date' => 'sometimes|date|after:today',
            'return_date' => 'sometimes|date|after:departure_date',
        ]);

        $tripRequest->update($validated);

        return new TripRequestResource($tripRequest->load('user'));
    }

    /**
     * Remove the specified trip request (cancel).
     */
    public function destroy(Request $request, TripRequest $tripRequest)
    {
        // Only the owner can cancel
        if ($tripRequest->user_id !== $request->user()->id) {
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

        // Send notification to requester
        if ($oldStatus !== $newStatus) {
            $tripRequest->user->notify(new TripRequestStatusChanged($tripRequest, $newStatus));
        }

        return new TripRequestResource($tripRequest->load('user'));
    }
}
