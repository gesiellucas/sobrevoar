<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\TripRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class DestinationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function getAuthToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    // ========================================
    // INDEX ENDPOINT TESTS (5 tests)
    // ========================================

    public function test_authenticated_user_can_view_all_destinations(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        // Create destinations
        Destination::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/destinations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'city', 'state', 'country', 'full_location']
                ]
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_unauthenticated_user_cannot_access_destinations(): void
    {
        $response = $this->getJson('/api/destinations');

        $response->assertStatus(401);
    }

    public function test_destinations_can_be_searched_by_city_state_or_country(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        // Create destinations with specific data
        Destination::factory()->create([
            'city' => 'São Paulo',
            'state' => 'SP',
            'country' => 'Brasil',
        ]);
        Destination::factory()->create([
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'country' => 'Brasil',
        ]);
        Destination::factory()->create([
            'city' => 'Paris',
            'state' => null,
            'country' => 'França',
        ]);

        // Search by city
        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/destinations?search=São Paulo');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['city' => 'São Paulo']);

        // Search by country
        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/destinations?search=Brasil');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Search by state
        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/destinations?search=RJ');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['city' => 'Rio de Janeiro']);
    }

    public function test_destinations_can_be_filtered_by_country(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        // Create Brazilian and international destinations
        Destination::factory()->count(3)->create(['country' => 'Brasil']);
        Destination::factory()->count(2)->create(['country' => 'França']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/destinations?country=Brasil');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_destinations_can_be_filtered_by_state(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        // Create destinations with different states
        Destination::factory()->count(2)->create([
            'state' => 'SP',
            'country' => 'Brasil',
        ]);
        Destination::factory()->count(3)->create([
            'state' => 'RJ',
            'country' => 'Brasil',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/destinations?state=SP');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    // ========================================
    // STORE ENDPOINT TESTS (3 tests)
    // ========================================

    public function test_admin_can_create_destination(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $destinationData = [
            'city' => 'Curitiba',
            'state' => 'PR',
            'country' => 'Brasil',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/destinations', $destinationData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'city', 'state', 'country', 'full_location']
            ])
            ->assertJson([
                'data' => [
                    'city' => 'Curitiba',
                    'state' => 'PR',
                    'country' => 'Brasil',
                ]
            ]);

        $this->assertDatabaseHas('destinations', [
            'city' => 'Curitiba',
            'state' => 'PR',
            'country' => 'Brasil',
        ]);
    }

    public function test_regular_user_cannot_create_destination(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        $destinationData = [
            'city' => 'Curitiba',
            'state' => 'PR',
            'country' => 'Brasil',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->postJson('/api/destinations', $destinationData);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('destinations', [
            'city' => 'Curitiba',
        ]);
    }

    public function test_destination_creation_validates_required_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        // Missing city
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/destinations', [
                'country' => 'Brasil',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city']);

        // Missing country
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/destinations', [
                'city' => 'São Paulo',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country']);

        // State is optional
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/destinations', [
                'city' => 'Paris',
                'country' => 'França',
            ]);

        $response->assertStatus(201);
    }

    // ========================================
    // SHOW ENDPOINT TESTS (2 tests)
    // ========================================

    public function test_authenticated_user_can_view_destination_details(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        $destination = Destination::factory()->create([
            'city' => 'Salvador',
            'state' => 'BA',
            'country' => 'Brasil',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson("/api/destinations/{$destination->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $destination->id,
                    'city' => 'Salvador',
                    'state' => 'BA',
                    'country' => 'Brasil',
                ]
            ]);
    }

    public function test_destination_show_includes_trip_requests_count(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        $destination = Destination::factory()->create();

        // Create trip requests for this destination
        TripRequest::factory()->count(3)->create([
            'destination_id' => $destination->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson("/api/destinations/{$destination->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.trip_requests_count', 3);
    }

    // ========================================
    // UPDATE ENDPOINT TESTS (2 tests)
    // ========================================

    public function test_admin_can_update_destination(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $destination = Destination::factory()->create([
            'city' => 'Old City',
            'state' => 'OC',
            'country' => 'Old Country',
        ]);

        $updateData = [
            'city' => 'New City',
            'state' => 'NC',
            'country' => 'New Country',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->patchJson("/api/destinations/{$destination->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'city' => 'New City',
                    'state' => 'NC',
                    'country' => 'New Country',
                ]
            ]);

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
            'city' => 'New City',
            'state' => 'NC',
            'country' => 'New Country',
        ]);
    }

    public function test_regular_user_cannot_update_destination(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        $destination = Destination::factory()->create([
            'city' => 'Original City',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->patchJson("/api/destinations/{$destination->id}", [
                'city' => 'Hacked City',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
            'city' => 'Original City',
        ]);
    }

    // ========================================
    // DESTROY ENDPOINT TESTS (2 tests)
    // ========================================

    public function test_admin_can_delete_destination_without_trip_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $destination = Destination::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->deleteJson("/api/destinations/{$destination->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Destination deleted successfully.',
            ]);

        $this->assertDatabaseMissing('destinations', [
            'id' => $destination->id,
        ]);
    }

    public function test_admin_cannot_delete_destination_with_trip_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $destination = Destination::factory()->create();

        // Create trip requests for this destination
        TripRequest::factory()->count(3)->create([
            'destination_id' => $destination->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->deleteJson("/api/destinations/{$destination->id}");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot delete destination with associated trip requests.',
                'trip_requests_count' => 3,
            ]);

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
        ]);
    }

    // ========================================
    // SPECIAL ENDPOINTS TESTS (3 tests)
    // ========================================

    public function test_authenticated_user_can_get_list_of_countries(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        // Create destinations in multiple countries
        Destination::factory()->create(['country' => 'Brasil']);
        Destination::factory()->create(['country' => 'Brasil']);
        Destination::factory()->create(['country' => 'França']);
        Destination::factory()->create(['country' => 'Estados Unidos']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/destinations/countries');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment(['Brasil'])
            ->assertJsonFragment(['França'])
            ->assertJsonFragment(['Estados Unidos']);
    }

    public function test_authenticated_user_can_get_list_of_states(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        // Create destinations with states
        Destination::factory()->create(['state' => 'SP', 'country' => 'Brasil']);
        Destination::factory()->create(['state' => 'SP', 'country' => 'Brasil']);
        Destination::factory()->create(['state' => 'RJ', 'country' => 'Brasil']);
        Destination::factory()->create(['state' => null, 'country' => 'França']); // No state

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/destinations/states');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data') // Only SP and RJ (null excluded)
            ->assertJsonFragment(['SP'])
            ->assertJsonFragment(['RJ']);
    }

    public function test_states_endpoint_can_be_filtered_by_country(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        // Create Brazilian destinations
        Destination::factory()->create(['state' => 'SP', 'country' => 'Brasil']);
        Destination::factory()->create(['state' => 'RJ', 'country' => 'Brasil']);

        // Create US destinations
        Destination::factory()->create(['state' => 'CA', 'country' => 'Estados Unidos']);
        Destination::factory()->create(['state' => 'NY', 'country' => 'Estados Unidos']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/destinations/states?country=Brasil');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['SP'])
            ->assertJsonFragment(['RJ']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/destinations/states?country=Estados Unidos');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['CA'])
            ->assertJsonFragment(['NY']);
    }
}
