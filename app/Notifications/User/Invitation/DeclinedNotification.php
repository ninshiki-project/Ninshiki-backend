<?php

namespace App\Notifications\User\Invitation;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeclinedNotification extends Notification
{
    public User $user;

    public Invitation $invitation;

    public function __construct(User $user, Invitation $invitation)
    {
        $this->user = $user;
        $this->invitation = $invitation;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invitation declined')
            ->greeting('Hello '.$this->user->name.'!')
            ->line('We regret to inform you that the invitation you sent to '.$this->invitation->email.' has been declined.')
            ->line('Thank you for using our platform.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}