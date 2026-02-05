<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Traveler;
use App\Models\TripRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TripRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function getAuthToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    public function test_authenticated_user_can_create_trip_request(): void
    {
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $destination = Destination::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/trip-requests', [
                'destination_id' => $destination->id,
                'departure_datetime' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'return_datetime' => now()->addDays(20)->format('Y-m-d H:i:s'),
                'description' => 'Test description',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('trip_requests', [
            'traveler_id' => $traveler->id,
            'destination_id' => $destination->id,
            'status' => 'requested',
        ]);
    }

    public function test_user_can_view_their_own_trip_requests(): void
    {
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $token = $this->getAuthToken($user);

        TripRequest::factory()->count(3)->create(['traveler_id' => $traveler->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/trip-requests');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_cannot_view_other_users_trip_requests(): void
    {
        $user = User::factory()->create();
        Traveler::factory()->forUser($user)->create();

        $otherUser = User::factory()->create();
        $otherTraveler = Traveler::factory()->forUser($otherUser)->create();
        $token = $this->getAuthToken($user);

        TripRequest::factory()->count(3)->create(['traveler_id' => $otherTraveler->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/trip-requests');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_admin_can_view_all_trip_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $adminTraveler = Traveler::factory()->forUser($admin)->create();
        $userTraveler = Traveler::factory()->forUser($user)->create();
        $token = $this->getAuthToken($admin);

        TripRequest::factory()->count(3)->create(['traveler_id' => $userTraveler->id]);
        TripRequest::factory()->count(2)->create(['traveler_id' => $adminTraveler->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/trip-requests');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_user_can_delete_their_requested_trip_request(): void
    {
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $token = $this->getAuthToken($user);

        $tripRequest = TripRequest::factory()->requested()->create([
            'traveler_id' => $traveler->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/trip-requests/{$tripRequest->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('trip_requests', [
            'id' => $tripRequest->id,
        ]);
    }

    public function test_user_cannot_delete_approved_trip_request(): void
    {
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $token = $this->getAuthToken($user);

        $tripRequest = TripRequest::factory()->approved()->create([
            'traveler_id' => $traveler->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/trip-requests/{$tripRequest->id}");

        $response->assertStatus(422);
    }

    public function test_admin_can_update_trip_request_status(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $token = $this->getAuthToken($admin);

        $tripRequest = TripRequest::factory()->requested()->create([
            'traveler_id' => $traveler->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/trip-requests/{$tripRequest->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('trip_requests', [
            'id' => $tripRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_regular_user_cannot_update_trip_request_status(): void
    {
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $token = $this->getAuthToken($user);

        $tripRequest = TripRequest::factory()->requested()->create([
            'traveler_id' => $traveler->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/trip-requests/{$tripRequest->id}/status", [
                'status' => 'approved',
            ]);

        $response->assertStatus(403);
    }

    public function test_trip_request_filters_work_correctly(): void
    {
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $token = $this->getAuthToken($user);

        $destination1 = Destination::factory()->create(['city' => 'Paris']);
        $destination2 = Destination::factory()->create(['city' => 'Tokyo']);

        TripRequest::factory()->requested()->create([
            'traveler_id' => $traveler->id,
            'destination_id' => $destination1->id,
        ]);

        TripRequest::factory()->approved()->create([
            'traveler_id' => $traveler->id,
            'destination_id' => $destination2->id,
        ]);

        // Filter by status
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/trip-requests?status=requested');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Filter by destination_id
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/trip-requests?destination_id={$destination1->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    // ========================================
    // UPDATE ENDPOINT TESTS (4 tests)
    // ========================================

    public function test_user_can_update_their_own_requested_trip_request(): void
    {
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $token = $this->getAuthToken($user);

        $destination = Destination::factory()->create();
        $newDestination = Destination::factory()->create();

        $tripRequest = TripRequest::factory()->requested()->create([
            'traveler_id' => $traveler->id,
            'destination_id' => $destination->id,
            'description' => 'Old description',
        ]);

        $updateData = [
            'destination_id' => $newDestination->id,
            'description' => 'New description',
            'departure_datetime' => now()->addDays(15)->format('Y-m-d H:i:s'),
            'return_datetime' => now()->addDays(25)->format('Y-m-d H:i:s'),
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/trip-requests/{$tripRequest->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('trip_requests', [
            'id' => $tripRequest->id,
            'destination_id' => $newDestination->id,
            'description' => 'New description',
        ]);
    }

    public function test_user_cannot_update_approved_trip_request(): void
    {
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $token = $this->getAuthToken($user);

        $destination = Destination::factory()->create();
        $tripRequest = TripRequest::factory()->approved()->create([
            'traveler_id' => $traveler->id,
            'destination_id' => $destination->id,
        ]);

        $newDestination = Destination::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/trip-requests/{$tripRequest->id}", [
                'destination_id' => $newDestination->id,
            ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('trip_requests', [
            'id' => $tripRequest->id,
            'destination_id' => $destination->id,
        ]);
    }

    public function test_user_cannot_update_other_users_trip_request(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $traveler1 = Traveler::factory()->forUser($user1)->create();
        $traveler2 = Traveler::factory()->forUser($user2)->create();
        $token1 = $this->getAuthToken($user1);

        $tripRequest = TripRequest::factory()->requested()->create([
            'traveler_id' => $traveler2->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->patchJson("/api/trip-requests/{$tripRequest->id}", [
                'description' => 'Hacked description',
            ]);

        $response->assertStatus(403);
    }

    public function test_trip_request_update_validates_dates(): void
    {
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $token = $this->getAuthToken($user);

        $tripRequest = TripRequest::factory()->requested()->create([
            'traveler_id' => $traveler->id,
        ]);

        // Try to update with past departure date
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/trip-requests/{$tripRequest->id}", [
                'departure_datetime' => now()->subDays(5)->format('Y-m-d H:i:s'),
                'return_datetime' => now()->addDays(10)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['departure_datetime']);

        // Try to update with return date before departure
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/trip-requests/{$tripRequest->id}", [
                'departure_datetime' => now()->addDays(20)->format('Y-m-d H:i:s'),
                'return_datetime' => now()->addDays(10)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['return_datetime']);
    }

    // ========================================
    // AUTHORIZATION EDGE CASES (3 tests)
    // ========================================

    public function test_admin_can_create_trip_request_for_specific_traveler(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $destination = Destination::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/trip-requests', [
                'traveler_id' => $traveler->id,
                'destination_id' => $destination->id,
                'departure_datetime' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'return_datetime' => now()->addDays(20)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('trip_requests', [
            'traveler_id' => $traveler->id,
            'destination_id' => $destination->id,
            'status' => 'requested',
        ]);
    }

    public function test_regular_user_cannot_specify_traveler_id(): void
    {
        $user1 = User::factory()->create();
        $user1Token = $this->getAuthToken($user1);
        $traveler1 = Traveler::factory()->forUser($user1)->create();

        $user2 = User::factory()->create();
        $traveler2 = Traveler::factory()->forUser($user2)->create();

        $destination = Destination::factory()->create();

        // User tries to create trip for another traveler
        $response = $this->withHeader('Authorization', 'Bearer ' . $user1Token)
            ->postJson('/api/trip-requests', [
                'traveler_id' => $traveler2->id,  // Try to use another traveler
                'destination_id' => $destination->id,
                'departure_datetime' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'return_datetime' => now()->addDays(20)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);

        // Verify trip was created for user's own traveler, not the specified one
        $this->assertDatabaseHas('trip_requests', [
            'traveler_id' => $traveler1->id,  // Should use user's own traveler
            'destination_id' => $destination->id,
        ]);

        $this->assertDatabaseMissing('trip_requests', [
            'traveler_id' => $traveler2->id,  // Should NOT use the specified traveler
        ]);
    }

    public function test_user_without_active_traveler_profile_cannot_create_trip_request(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);
        $destination = Destination::factory()->create();

        // User has no traveler profile

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->postJson('/api/trip-requests', [
                'destination_id' => $destination->id,
                'departure_datetime' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'return_datetime' => now()->addDays(20)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422);

        // Try with inactive traveler
        Traveler::factory()->inactive()->forUser($user)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->postJson('/api/trip-requests', [
                'destination_id' => $destination->id,
                'departure_datetime' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'return_datetime' => now()->addDays(20)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(422);
    }
}
