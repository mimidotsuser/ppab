<?php

namespace App\Notifications\PurchaseRequest;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private PurchaseRequest $request;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(PurchaseRequest $purchaseRequest)
    {
        $this->request = $purchaseRequest;
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
            . config('weburls.purchase_requests.history') . '/' . $this->request->id);


        return (new MailMessage)
            ->subject('Purchase Request ' . $this->request->sn . ' Submitted')
            ->greeting('Dear ' . $notifiable->first_name . ' ' . $notifiable->last_name)
            ->line('Your purchase requisition form ' . $this->request->sn
                . ' has been submitted successfully.')
            ->action('View Request', $url)
            ->line('We will update you of the outcome.')
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
