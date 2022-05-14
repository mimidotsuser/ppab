<?php

namespace App\Notifications\MaterialRequisition;

use App\Models\MaterialRequisition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private MaterialRequisition $request;
    private bool $fullyIssued;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(MaterialRequisition $requisition, $fullyIssued = true)
    {
        $this->request = $requisition;
        $this->fullyIssued = $fullyIssued;
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
            . config('weburls.material_requests.history') . '/' . $this->request->id);

        $name = $this->request->createdBy->first_name . ' ' . $this->request->createdBy->last_name;

        return (new MailMessage)
            ->subject('Re: Material Request ' . $this->request->sn)
            ->greeting('Dear ' . $notifiable->first_name . ' ' . $notifiable->last_name)
            ->line('Your material requisition form (' . $this->request->sn
                . ') issuance status has been updated')
            ->action('Generate Store Issue Note', $url)
            ->when($this->fullyIssued, function ($mail) {
                $mail->line('All approved items have been issued to you successfully.');
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
