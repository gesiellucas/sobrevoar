<?php

namespace Tests\Feature;

use App\Models\TripRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function getAuthToken(User $user): string
    {
        return $user->createToken('test-token')->plainTextToken;
    }

    public function test_authenticated_user_can_create_trip_request(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/trip-requests', [
                'requester_name' => $user->name,
                'destination' => 'Paris, France',
                'departure_date' => now()->addDays(10)->format('Y-m-d'),
                'return_date' => now()->addDays(20)->format('Y-m-d'),
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'destination',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('trip_requests', [
            'user_id' => $user->id,
            'destination' => 'Paris, France',
            'status' => 'requested',
        ]);
    }

    public function test_user_can_view_their_own_trip_requests(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);

        TripRequest::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/trip-requests');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_cannot_view_other_users_trip_requests(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = $this->getAuthToken($user);

        TripRequest::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/trip-requests');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_admin_can_view_all_trip_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $token = $this->getAuthToken($admin);

        TripRequest::factory()->count(3)->create(['user_id' => $user->id]);
        TripRequest::factory()->count(2)->create(['user_id' => $admin->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/trip-requests');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_user_can_delete_their_requested_trip_request(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);

        $tripRequest = TripRequest::factory()->requested()->create([
            'user_id' => $user->id,
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
        $token = $this->getAuthToken($user);

        $tripRequest = TripRequest::factory()->approved()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/trip-requests/{$tripRequest->id}");

        $response->assertStatus(422);
    }

    public function test_admin_can_update_trip_request_status(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $token = $this->getAuthToken($admin);

        $tripRequest = TripRequest::factory()->requested()->create([
            'user_id' => $user->id,
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
        $token = $this->getAuthToken($user);

        $tripRequest = TripRequest::factory()->requested()->create([
            'user_id' => $user->id,
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
        $token = $this->getAuthToken($user);

        TripRequest::factory()->requested()->create([
            'user_id' => $user->id,
            'destination' => 'Paris, France',
        ]);

        TripRequest::factory()->approved()->create([
            'user_id' => $user->id,
            'destination' => 'Tokyo, Japan',
        ]);

        // Filter by status
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/trip-requests?status=requested');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Filter by destination
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/trip-requests?destination=Paris');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
