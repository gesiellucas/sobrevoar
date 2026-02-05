<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTravelerRequest;
use App\Http\Requests\UpdateTravelerRequest;
use App\Http\Resources\TravelerResource;
use App\Models\Traveler;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TravelerController extends Controller
{
    /**
     * Display a listing of travelers.
     */
    public function index(Request $request)
    {
        $query = Traveler::with('user');

        // If not admin, only show own traveler profile
        if (!$request->user()->is_admin) {
            $query->where('user_id', $request->user()->id);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('email', 'like', "%{$search}%");
                    });
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $travelers = $query->latest()->paginate($perPage);

        return TravelerResource::collection($travelers);
    }

    /**
     * Store a newly created traveler with user.
     */
    public function store(StoreTravelerRequest $request)
    {
        // Only admins can create travelers
        if (!$request->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        return DB::transaction(function () use ($request) {
            // Create user for login
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => $request->boolean('is_admin', false),
            ]);

            // Create traveler profile
            $traveler = Traveler::create([
                'name' => $request->name,
                'user_id' => $user->id,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return new TravelerResource($traveler->load('user'));
        });
    }

    /**
     * Display the specified traveler.
     */
    public function show(Request $request, Traveler $traveler)
    {
        // Check authorization: admin can see all, user can only see own profile
        if (!$request->user()->is_admin && $traveler->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        return new TravelerResource($traveler->load('user'));
    }

    /**
     * Update the specified traveler.
     */
    public function update(UpdateTravelerRequest $request, Traveler $traveler)
    {
        // Only admins can update travelers
        if (!$request->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        return DB::transaction(function () use ($request, $traveler) {
            // Update traveler
            $traveler->update([
                'name' => $request->input('name', $traveler->name),
                'is_active' => $request->has('is_active')
                    ? $request->boolean('is_active')
                    : $traveler->is_active,
            ]);

            // Update user if needed
            $user = $traveler->user;
            $userData = [];

            if ($request->filled('name')) {
                $userData['name'] = $request->name;
            }

            if ($request->filled('email')) {
                $userData['email'] = $request->email;
            }

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            if ($request->has('is_admin')) {
                $userData['is_admin'] = $request->boolean('is_admin');
            }

            if (!empty($userData)) {
                $user->update($userData);
            }

            return new TravelerResource($traveler->fresh()->load('user'));
        });
    }

    /**
     * Remove the specified traveler (soft delete by deactivating).
     */
    public function destroy(Request $request, Traveler $traveler)
    {
        // Only admins can delete travelers
        if (!$request->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        // Check if traveler has pending trip requests
        $pendingRequests = $traveler->tripRequests()
            ->where('status', 'requested')
            ->count();

        if ($pendingRequests > 0) {
            return response()->json([
                'message' => 'Cannot delete traveler with pending trip requests.',
                'pending_requests' => $pendingRequests,
            ], 422);
        }

        // Deactivate instead of hard delete
        $traveler->update(['is_active' => false]);

        return response()->json([
            'message' => 'Traveler deactivated successfully.',
        ]);
    }

    /**
     * Restore a deactivated traveler.
     */
    public function restore(Request $request, Traveler $traveler)
    {
        // Only admins can restore travelers
        if (!$request->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $traveler->update(['is_active' => true]);

        return new TravelerResource($traveler->load('user'));
    }
}
