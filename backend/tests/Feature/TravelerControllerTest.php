<?php

namespace Tests\Feature;

use App\Models\Traveler;
use App\Models\TripRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TravelerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function getAuthToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    // ========================================
    // INDEX ENDPOINT TESTS (5 tests)
    // ========================================

    public function test_admin_can_view_all_travelers(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        // Create multiple travelers
        $traveler1 = Traveler::factory()->create();
        $traveler2 = Traveler::factory()->create();
        $traveler3 = Traveler::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/travelers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'is_active', 'user']
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_regular_user_can_only_view_own_traveler_profile(): void
    {
        // Create first user with traveler
        $user1 = User::factory()->create();
        $traveler1 = Traveler::factory()->forUser($user1)->create();
        $token1 = $this->getAuthToken($user1);

        // Create other users with travelers
        Traveler::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->getJson('/api/travelers');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $traveler1->id]
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_access_travelers_index(): void
    {
        $response = $this->getJson('/api/travelers');

        $response->assertStatus(401);
    }

    public function test_travelers_can_be_filtered_by_active_status(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        // Create active and inactive travelers
        Traveler::factory()->active()->count(2)->create();
        Traveler::factory()->inactive()->count(3)->create();

        // Filter by active
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/travelers?is_active=true');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        // Filter by inactive
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/travelers?is_active=false');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_travelers_can_be_searched_by_name_or_email(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        // Create travelers with specific data
        $user1 = User::factory()->create(['email' => 'john.doe@example.com']);
        $traveler1 = Traveler::factory()->forUser($user1)->create(['name' => 'John Doe']);

        $user2 = User::factory()->create(['email' => 'jane.smith@example.com']);
        $traveler2 = Traveler::factory()->forUser($user2)->create(['name' => 'Jane Smith']);

        // Search by name
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/travelers?search=John');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['name' => 'John Doe']
                ]
            ]);

        // Search by email
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/travelers?search=jane.smith');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    ['name' => 'Jane Smith']
                ]
            ]);
    }

    // ========================================
    // STORE ENDPOINT TESTS (4 tests)
    // ========================================

    public function test_admin_can_create_traveler_with_user(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $travelerData = [
            'name' => 'New Traveler',
            'email' => 'newtraveler@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
            'is_admin' => false,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/travelers', $travelerData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'is_active', 'user']
            ]);

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'newtraveler@example.com',
            'name' => 'New Traveler',
            'is_admin' => false,
        ]);

        // Verify traveler was created
        $this->assertDatabaseHas('travelers', [
            'name' => 'New Traveler',
            'is_active' => true,
        ]);

        // Verify password is hashed
        $user = User::where('email', 'newtraveler@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_regular_user_cannot_create_traveler(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        $travelerData = [
            'name' => 'New Traveler',
            'email' => 'newtraveler@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->postJson('/api/travelers', $travelerData);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('users', [
            'email' => 'newtraveler@example.com',
        ]);
    }

    public function test_admin_can_create_traveler_with_admin_flag(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $travelerData = [
            'name' => 'Admin Traveler',
            'email' => 'admintraveler@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_admin' => true,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/travelers', $travelerData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'admintraveler@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_traveler_creation_validates_required_fields(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        // Missing name
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/travelers', [
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Missing email
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/travelers', [
                'name' => 'Test User',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Duplicate email
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/travelers', [
                'name' => 'Test User',
                'email' => 'existing@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // ========================================
    // SHOW ENDPOINT TESTS (3 tests)
    // ========================================

    public function test_admin_can_view_any_traveler_profile(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $otherUser = User::factory()->create();
        $otherTraveler = Traveler::factory()->forUser($otherUser)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson("/api/travelers/{$otherTraveler->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $otherTraveler->id,
                    'name' => $otherTraveler->name,
                ]
            ]);
    }

    public function test_user_can_view_own_traveler_profile(): void
    {
        $user = User::factory()->create();
        $traveler = Traveler::factory()->forUser($user)->create();
        $userToken = $this->getAuthToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson("/api/travelers/{$traveler->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $traveler->id,
                    'name' => $traveler->name,
                ]
            ]);
    }

    public function test_user_cannot_view_other_users_traveler_profile(): void
    {
        $user1 = User::factory()->create();
        $user1Token = $this->getAuthToken($user1);

        $user2 = User::factory()->create();
        $traveler2 = Traveler::factory()->forUser($user2)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $user1Token)
            ->getJson("/api/travelers/{$traveler2->id}");

        $response->assertStatus(403);
    }

    // ========================================
    // UPDATE ENDPOINT TESTS (3 tests)
    // ========================================

    public function test_admin_can_update_traveler_profile(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $user = User::factory()->create(['email' => 'old@example.com']);
        $traveler = Traveler::factory()->forUser($user)->create(['name' => 'Old Name']);

        $updateData = [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->patchJson("/api/travelers/{$traveler->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'New Name',
                ]
            ]);

        $this->assertDatabaseHas('travelers', [
            'id' => $traveler->id,
            'name' => 'New Name',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
        ]);

        // Verify password was updated and hashed
        $updatedUser = User::find($user->id);
        $this->assertTrue(Hash::check('newpassword123', $updatedUser->password));
    }

    public function test_admin_can_toggle_traveler_active_status(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $traveler = Traveler::factory()->active()->create();

        // Deactivate
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->patchJson("/api/travelers/{$traveler->id}", [
                'is_active' => false,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('travelers', [
            'id' => $traveler->id,
            'is_active' => false,
        ]);

        // Reactivate
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->patchJson("/api/travelers/{$traveler->id}", [
                'is_active' => true,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('travelers', [
            'id' => $traveler->id,
            'is_active' => true,
        ]);
    }

    public function test_regular_user_cannot_update_traveler(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        $traveler = Traveler::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->patchJson("/api/travelers/{$traveler->id}", [
                'name' => 'Hacked Name',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('travelers', [
            'id' => $traveler->id,
            'name' => 'Hacked Name',
        ]);
    }

    // ========================================
    // DESTROY ENDPOINT TESTS (3 tests)
    // ========================================

    public function test_admin_can_deactivate_traveler_without_pending_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $traveler = Traveler::factory()->active()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->deleteJson("/api/travelers/{$traveler->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Traveler deactivated successfully.',
            ]);

        $this->assertDatabaseHas('travelers', [
            'id' => $traveler->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_cannot_delete_traveler_with_pending_trip_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $traveler = Traveler::factory()->create();

        // Create pending trip requests
        TripRequest::factory()->requested()->count(2)->create([
            'traveler_id' => $traveler->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->deleteJson("/api/travelers/{$traveler->id}");

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot delete traveler with pending trip requests.',
                'pending_requests' => 2,
            ]);

        $this->assertDatabaseHas('travelers', [
            'id' => $traveler->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_delete_traveler_with_only_approved_or_cancelled_requests(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $traveler = Traveler::factory()->create();

        // Create approved and cancelled requests (not pending)
        TripRequest::factory()->approved()->create([
            'traveler_id' => $traveler->id,
        ]);
        TripRequest::factory()->cancelled()->create([
            'traveler_id' => $traveler->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->deleteJson("/api/travelers/{$traveler->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('travelers', [
            'id' => $traveler->id,
            'is_active' => false,
        ]);
    }

    // ========================================
    // RESTORE ENDPOINT TESTS (2 tests)
    // ========================================

    public function test_admin_can_restore_deactivated_traveler(): void
    {
        $admin = User::factory()->admin()->create();
        $adminToken = $this->getAuthToken($admin);

        $traveler = Traveler::factory()->inactive()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->patchJson("/api/travelers/{$traveler->id}/restore");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $traveler->id,
                    'is_active' => true,
                ]
            ]);

        $this->assertDatabaseHas('travelers', [
            'id' => $traveler->id,
            'is_active' => true,
        ]);
    }

    public function test_regular_user_cannot_restore_traveler(): void
    {
        $user = User::factory()->create();
        $userToken = $this->getAuthToken($user);

        $traveler = Traveler::factory()->inactive()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->patchJson("/api/travelers/{$traveler->id}/restore");

        $response->assertStatus(403);

        $this->assertDatabaseHas('travelers', [
            'id' => $traveler->id,
            'is_active' => false,
        ]);
    }
}
