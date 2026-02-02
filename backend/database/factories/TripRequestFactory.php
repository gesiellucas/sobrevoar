<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TripRequest>
 */
class TripRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureDate = fake()->dateTimeBetween('now', '+6 months');
        $returnDate = fake()->dateTimeBetween($departureDate, '+1 year');

        return [
            'user_id' => User::factory(),
            'requester_name' => fake()->name(),
            'destination' => fake()->randomElement([
                'Paris, France',
                'Tokyo, Japan',
                'New York, USA',
                'London, UK',
                'Barcelona, Spain',
                'Dubai, UAE',
                'Sydney, Australia',
                'Singapore',
                'Rome, Italy',
                'Amsterdam, Netherlands',
                'Berlin, Germany',
                'Seoul, South Korea',
                'Bangkok, Thailand',
                'Istanbul, Turkey',
                'Los Angeles, USA',
                'Miami, USA',
                'Vancouver, Canada',
                'Mexico City, Mexico',
                'Buenos Aires, Argentina',
                'Lisbon, Portugal',
            ]),
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
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
}
