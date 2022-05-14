<?php

namespace App\Notifications\GoodsReceivedNote;

use App\Models\GoodsReceiptNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private GoodsReceiptNote $request;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(GoodsReceiptNote $goodsReceiptNote)
    {
        $this->request = $goodsReceiptNote;
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
            . config('weburls.goods_received_note.history') . '/' . $this->request->id);


        return (new MailMessage)
            ->subject('Goods Received Note ' . $this->request->sn)
            ->greeting('Dear ' . $notifiable->first_name . ' ' . $notifiable->last_name)
            ->line('Your GRN form ' . $this->request->sn . ' has been submitted successfully.')
            ->action('View Request', $url)
            ->line('Request has been forwarded to the inspection team. ' .
                'We will notify you of the outcome.');

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
