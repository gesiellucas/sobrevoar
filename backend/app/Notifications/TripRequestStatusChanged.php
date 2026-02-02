<?php

namespace App\Notifications;

use App\Models\TripRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TripRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $tripRequest;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(TripRequest $tripRequest, string $newStatus)
    {
        $this->tripRequest = $tripRequest;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusText = match($this->newStatus) {
            'approved' => 'approved',
            'cancelled' => 'cancelled',
            default => 'updated',
        };

        $color = match($this->newStatus) {
            'approved' => 'success',
            'cancelled' => 'error',
            default => 'primary',
        };

        return (new MailMessage)
            ->subject('Trip Request Status Updated')
            ->greeting('Hello ' . $this->tripRequest->requester_name . '!')
            ->line("Your trip request to {$this->tripRequest->destination} has been {$statusText}.")
            ->line("**Trip Details:**")
            ->line("- Destination: {$this->tripRequest->destination}")
            ->line("- Departure: {$this->tripRequest->departure_date->format('F d, Y')}")
            ->line("- Return: {$this->tripRequest->return_date->format('F d, Y')}")
            ->line("- Status: " . ucfirst($this->newStatus))
            ->action('View Trip Request', url('/trip-requests/' . $this->tripRequest->id))
            ->line('Thank you for using Trip Request Manager!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'trip_request_id' => $this->tripRequest->id,
            'status' => $this->newStatus,
        ];
    }
}
