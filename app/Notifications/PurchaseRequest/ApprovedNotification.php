<?php

namespace App\Notifications\PurchaseRequest;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private PurchaseRequest $request;
    private bool $rejected;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(PurchaseRequest $purchaseRequest, bool $rejected = false)
    {
        $this->request = $purchaseRequest;
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
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url(config('weburls.root')
            . config('weburls.purchase_requests.history') . '/' . $this->request->id);


        return (new MailMessage)
            ->subject('Re: Purchase Request ' . $this->request->sn)
            ->greeting('Dear ' . $notifiable->first_name . ' ' . $notifiable->last_name)
            ->line('Your purchase requisition form (' . $this->request->sn
                . ') status has been updated.')
            ->when($this->rejected, function ($mail) use ($url) {
                $mail->line('No item was approved.');
            })
            ->action('View Request', $url)
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
