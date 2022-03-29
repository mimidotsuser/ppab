<?php

namespace App\Notifications;

use App\Models\MaterialRequisition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MRFVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $request;
    private $rejected;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(MaterialRequisition $requisition, bool $rejected = false)
    {
        $this->request = $requisition;
        $this->rejected = $rejected;
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
            . config('weburls.material_requests.history') . '?id=' . $this->request->id);

        $name = $this->request->createdBy->first_name . ' ' . $this->request->createdBy->last_name;

        return (new MailMessage)
            ->subject('Material Request Form Verification Update')
            ->greeting('Dear ' . $name)
            ->line('Your material requisition form (' . $this->request->sn
                . ') status has been updated')
            ->when($this->rejected, function ($mail) use ($url) {
                $mail->action('View Request', $url);
            })
            ->when(!$this->rejected, function ($mail) use ($url) {
                $mail->line('The request is now pending approval.');
            })
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
