<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $destinations = [
            // Brasil
            ['city' => 'São Paulo', 'state' => 'SP', 'country' => 'Brasil'],
            ['city' => 'Rio de Janeiro', 'state' => 'RJ', 'country' => 'Brasil'],
            ['city' => 'Belo Horizonte', 'state' => 'MG', 'country' => 'Brasil'],
            ['city' => 'Salvador', 'state' => 'BA', 'country' => 'Brasil'],
            ['city' => 'Curitiba', 'state' => 'PR', 'country' => 'Brasil'],
            ['city' => 'Fortaleza', 'state' => 'CE', 'country' => 'Brasil'],
            ['city' => 'Recife', 'state' => 'PE', 'country' => 'Brasil'],
            ['city' => 'Porto Alegre', 'state' => 'RS', 'country' => 'Brasil'],
            ['city' => 'Brasília', 'state' => 'DF', 'country' => 'Brasil'],
            ['city' => 'Manaus', 'state' => 'AM', 'country' => 'Brasil'],

            // Internacional
            ['city' => 'New York', 'state' => 'NY', 'country' => 'Estados Unidos'],
            ['city' => 'Los Angeles', 'state' => 'CA', 'country' => 'Estados Unidos'],
            ['city' => 'Miami', 'state' => 'FL', 'country' => 'Estados Unidos'],
            ['city' => 'Paris', 'state' => null, 'country' => 'França'],
            ['city' => 'Londres', 'state' => null, 'country' => 'Reino Unido'],
            ['city' => 'Lisboa', 'state' => null, 'country' => 'Portugal'],
            ['city' => 'Buenos Aires', 'state' => null, 'country' => 'Argentina'],
            ['city' => 'Santiago', 'state' => null, 'country' => 'Chile'],
            ['city' => 'Tóquio', 'state' => null, 'country' => 'Japão'],
            ['city' => 'Dubai', 'state' => null, 'country' => 'Emirados Árabes'],
        ];

        foreach ($destinations as $destination) {
            Destination::create($destination);
        }
    }
}
