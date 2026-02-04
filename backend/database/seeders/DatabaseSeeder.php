<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar usuários base
        $this->createUsers();

        // 2. Criar destinos (independente)
        $this->call(DestinationSeeder::class);

        // 3. Criar viajantes (depende de usuários)
        $this->call(TravelerSeeder::class);

        // 4. Criar solicitações de viagem (depende de viajantes e destinos)
        $this->call(TripRequestSeeder::class);

        // 5. Criar notificações (depende de usuários)
        $this->call(UserNotificationSeeder::class);

        $this->command->info('');
        $this->command->info('Database seeded successfully!');
        $this->command->info('=================================');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Test User: test@example.com / password');
    }

    /**
     * Create base users for the system.
     */
    private function createUsers(): void
    {
        // Create admin user
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create regular test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create 10 random users
        User::factory(10)->create();

        $this->command->info('Users created: ' . User::count());
    }
}
