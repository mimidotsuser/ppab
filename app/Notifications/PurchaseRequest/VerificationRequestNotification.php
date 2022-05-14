<?php

namespace App\Notifications\PurchaseRequest;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationRequestNotification extends Notification implements ShouldQueue
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
            . config('weburls.purchase_requests.verification') . '/' . $this->request->id);

        $author = $this->request->createdBy->first_name . ' ' . $this->request->createdBy->last_name;

        return (new MailMessage)
            ->subject('Purchase Request Verification Requested')
            ->greeting('Dear ' . $notifiable->first_name . ' ' . $notifiable->last_name)
            ->line('Purchase request form (' . $this->request->sn . ') by ' . $author
                . ' requires your attention')
            ->action('Click here to action the request', $url)
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
