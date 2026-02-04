<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Traveler;
use App\Models\TripRequest;
use Illuminate\Database\Seeder;

class TripRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $travelers = Traveler::where('is_active', true)->get();
        $destinations = Destination::all();

        foreach ($travelers as $traveler) {
            // Cada viajante tem entre 2 e 5 solicitações
            $requestCount = rand(2, 5);

            for ($i = 0; $i < $requestCount; $i++) {
                TripRequest::factory()
                    ->forTraveler($traveler)
                    ->forDestination($destinations->random())
                    ->create();
            }
        }
    }
}
