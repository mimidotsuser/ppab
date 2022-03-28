<?php

namespace App\Notifications;

use App\Models\MaterialRequisition;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MRFVerificationRequestedNotification extends Notification
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(config('weburls.root')
            . config('weburls.material_requests.verification') . '/' . $this->request->id);

        $author = $this->request->createdBy->first_name . ' ' . $this->request->createdBy->last_name;

        return (new MailMessage)
            ->subject('Material Request Form Pending Verification')
            ->line('Hello,')
            ->line('Material requisition form (' . $this->request->sn . ') by ' . $author
                . ' requires your attention')
            ->action('Click here to action the request', $url)
            ->line('Kind regards.')
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
