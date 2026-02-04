<?php

namespace Database\Factories;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Destination>
 */
class DestinationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Destination::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $destinations = [
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

        $destination = fake()->randomElement($destinations);

        return [
            'city' => $destination['city'],
            'state' => $destination['state'],
            'country' => $destination['country'],
        ];
    }

    /**
     * Create a Brazilian destination.
     */
    public function brazilian(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => 'Brasil',
        ]);
    }

    /**
     * Create an international destination.
     */
    public function international(): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => fake()->randomElement(['Estados Unidos', 'França', 'Reino Unido', 'Portugal', 'Argentina']),
        ]);
    }
}
