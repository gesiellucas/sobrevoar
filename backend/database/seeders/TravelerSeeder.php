<?php

namespace Database\Seeders;

use App\Models\Traveler;
use App\Models\User;
use Illuminate\Database\Seeder;

class TravelerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar viajantes para cada usuário existente
        $users = User::all();

        foreach ($users as $user) {
            // Cada usuário tem pelo menos um viajante (ele mesmo)
            Traveler::create([
                'name' => $user->name,
                'is_active' => true,
                'user_id' => $user->id,
            ]);

            // Alguns usuários podem ter viajantes adicionais (dependentes, colegas, etc.)
            if (rand(0, 1)) {
                Traveler::factory()
                    ->count(rand(1, 2))
                    ->forUser($user)
                    ->create();
            }
        }
    }
}
