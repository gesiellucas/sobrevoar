<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;

class UserNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Cada usuário recebe entre 2 e 6 notificações
            $notificationCount = rand(2, 6);

            UserNotification::factory($notificationCount)
                ->forUser($user)
                ->create();
        }
    }
}
