<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TripRequest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create regular test user
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create 10 random users
        $users = User::factory(10)->create();

        // Add test user and admin to users collection
        $allUsers = $users->push($testUser, $admin);

        // Create 60 trip requests distributed among users
        // 20 requested, 25 approved, 15 cancelled
        foreach ($allUsers as $user) {
            // Each user gets 4-6 trip requests
            $requestCount = rand(4, 6);

            TripRequest::factory($requestCount)->create([
                'user_id' => $user->id,
                'requester_name' => $user->name,
            ]);
        }

        // Create some specific trip requests for the test user
        TripRequest::factory()->requested()->create([
            'user_id' => $testUser->id,
            'requester_name' => $testUser->name,
            'destination' => 'Paris, France',
        ]);

        TripRequest::factory()->approved()->create([
            'user_id' => $testUser->id,
            'requester_name' => $testUser->name,
            'destination' => 'Tokyo, Japan',
        ]);

        TripRequest::factory()->cancelled()->create([
            'user_id' => $testUser->id,
            'requester_name' => $testUser->name,
            'destination' => 'New York, USA',
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Test User: test@example.com / password');
    }
}
