<?php

namespace Database\Factories;

use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserNotification>
 */
class UserNotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $messages = [
            'Sua solicitação de viagem foi aprovada.',
            'Sua solicitação de viagem foi cancelada.',
            'Nova solicitação de viagem pendente de aprovação.',
            'Lembrete: sua viagem está agendada para amanhã.',
            'Alteração no status da sua solicitação de viagem.',
            'Bem-vindo ao sistema de gerenciamento de viagens!',
            'Seu perfil de viajante foi atualizado.',
            'Nova política de viagens disponível.',
        ];

        return [
            'message' => fake()->randomElement($messages),
            'is_checked' => fake()->boolean(30), // 30% chance de já estar lida
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unchecked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_checked' => false,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function checked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_checked' => true,
        ]);
    }

    /**
     * Associate with a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
