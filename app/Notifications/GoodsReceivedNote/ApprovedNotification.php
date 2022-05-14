<?php

namespace App\Notifications\GoodsReceivedNote;

use App\Models\GoodsReceiptNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private GoodsReceiptNote $request;
    private bool $rejected;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(GoodsReceiptNote $goodsReceiptNote, bool $rejected = false)
    {
        $this->request = $goodsReceiptNote;
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
            . config('weburls.goods_received_note.history') . '/' . $this->request->id);


        return (new MailMessage)
            ->subject('Re: Goods Received Note ' . $this->request->sn)
            ->greeting('Dear ' . $notifiable->first_name . ' ' . $notifiable->last_name)
            ->line('Your GRN form ' . $this->request->sn . ' approval status has been updated.')
            ->when($this->rejected, function ($mail) {
                $mail->line('The RGA/GRN documents were rejected. You are requested  to restart' .
                    ' the process by re-creating the request.');
            })
            ->action('View Request', $url);

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
