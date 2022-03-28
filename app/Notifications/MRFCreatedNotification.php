<?php

namespace App\Notifications;

use App\Models\MaterialRequisition;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MRFCreatedNotification extends Notification
{
    use Queueable;

    private $request;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(MaterialRequisition $requisition)
    {
        $this->request = $requisition;
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

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(config('weburls.root')
            . config('weburls.material_requests.history') . '?id=' . $this->request->id);

        $name = $this->request->createdBy->first_name . ' ' . $this->request->createdBy->last_name;

        return (new MailMessage)
            ->subject('Material Request Form Submitted')
            ->line('Dear ' . $name)
            ->line('Your material requisition form ' . $this->request->sn
                . ' has been submitted successfully.')
            ->action('View Request', $url)
            ->line('We will notify you of the outcome.')
            ->line('Kind regards')
            ->withSymfonyMessage(function ($mail) {
                $id = $this->request->email_thread_id;

                $mail->getHeaders()->addTextHeader('In-Reply-To', '<' . $id . '>');
                $mail->getHeaders()->addTextHeader('References', '<' . $id . '>');
            });

    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
