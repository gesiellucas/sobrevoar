<?php

namespace Database\Factories;

use App\Models\Destination;
use App\Models\Traveler;
use App\Models\TripRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripRequest>
 */
class TripRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TripRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureDatetime = fake()->dateTimeBetween('now', '+6 months');
        $returnDatetime = fake()->dateTimeBetween($departureDatetime, '+1 year');

        $descriptions = [
            'Viagem de negócios para reunião com cliente.',
            'Participação em conferência anual.',
            'Treinamento técnico na filial.',
            'Visita a fornecedores.',
            'Feira de tecnologia.',
            'Reunião de planejamento estratégico.',
            'Auditoria em unidade regional.',
            'Workshop de capacitação.',
            'Evento de lançamento de produto.',
            'Encontro com parceiros comerciais.',
            null,
        ];

        return [
            'description' => fake()->randomElement($descriptions),
            'traveler_id' => Traveler::factory(),
            'destination_id' => Destination::factory(),
            'departure_datetime' => $departureDatetime,
            'return_datetime' => $returnDatetime,
            'status' => fake()->randomElement(['requested', 'approved', 'cancelled']),
        ];
    }

    /**
     * Indicate that the trip request is in requested status.
     */
    public function requested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'requested',
        ]);
    }

    /**
     * Indicate that the trip request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the trip request is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Associate with a specific traveler.
     */
    public function forTraveler(Traveler $traveler): static
    {
        return $this->state(fn (array $attributes) => [
            'traveler_id' => $traveler->id,
        ]);
    }

    /**
     * Associate with a specific destination.
     */
    public function forDestination(Destination $destination): static
    {
        return $this->state(fn (array $attributes) => [
            'destination_id' => $destination->id,
        ]);
    }
}
