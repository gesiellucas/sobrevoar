<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDestinationRequest;
use App\Http\Requests\UpdateDestinationRequest;
use App\Http\Resources\DestinationResource;
use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    /**
     * Display a listing of destinations.
     */
    public function index(Request $request)
    {
        $query = Destination::query();

        // Search by city, state or country
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // Filter by country
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        // Filter by state
        if ($request->has('state')) {
            $query->where('state', $request->state);
        }

        // Pagination or all
        if ($request->boolean('all')) {
            return DestinationResource::collection($query->orderBy('country')->orderBy('city')->get());
        }

        $perPage = $request->input('per_page', 15);
        $destinations = $query->orderBy('country')->orderBy('state')->orderBy('city')->paginate($perPage);

        return DestinationResource::collection($destinations);
    }

    /**
     * Store a newly created destination.
     */
    public function store(StoreDestinationRequest $request)
    {
        $destination = Destination::create($request->validated());

        return new DestinationResource($destination);
    }

    /**
     * Display the specified destination.
     */
    public function show(Destination $destination)
    {
        return new DestinationResource($destination->loadCount('tripRequests'));
    }

    /**
     * Update the specified destination.
     */
    public function update(UpdateDestinationRequest $request, Destination $destination)
    {
        $destination->update($request->validated());

        return new DestinationResource($destination);
    }

    /**
     * Remove the specified destination.
     */
    public function destroy(Destination $destination)
    {
        // Check if destination has trip requests
        $tripRequestsCount = $destination->tripRequests()->count();

        if ($tripRequestsCount > 0) {
            return response()->json([
                'message' => 'Cannot delete destination with associated trip requests.',
                'trip_requests_count' => $tripRequestsCount,
            ], 422);
        }

        $destination->delete();

        return response()->json([
            'message' => 'Destination deleted successfully.',
        ]);
    }

    /**
     * Get list of unique countries.
     */
    public function countries()
    {
        $countries = Destination::distinct()
            ->orderBy('country')
            ->pluck('country');

        return response()->json(['data' => $countries]);
    }

    /**
     * Get list of states by country.
     */
    public function states(Request $request)
    {
        $query = Destination::distinct();

        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        $states = $query->whereNotNull('state')
            ->orderBy('state')
            ->pluck('state');

        return response()->json(['data' => $states]);
    }
}
