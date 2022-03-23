<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class UserInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $token;
    private $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, string $token)
    {
        $this->token = $token;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    protected function buildMailMessage($token)
    {
        $url = config('app.frontend_url') . '/account-recovery/' . $token .
            '?invite=true&email=' . $this->user->email;

        return (new MailMessage)
            ->subject(Lang::get('Account Setup Invitation'))
            ->line(Lang::get('You are receiving this email because you have been invited to join ')
                . config('app.name'))
            ->action(Lang::get('Set Password'), $url)
            ->line(Lang::get('This invitation link will expire in :count minutes.',
                ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]))
            ->line(Lang::get('If you were not expecting this invitation, you can ignore this email.'));
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return $this->buildMailMessage($this->token);
    }


}
