<?php

namespace App\Notifications\GoodsReceivedNote;

use App\Models\GoodsReceiptNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequestNotification extends Notification implements ShouldQueue
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
            . config('weburls.goods_received_note.approval')
            . '/' . $this->request->id . '/create');

        $author = $this->request->createdBy->first_name . ' ' . $this->request->createdBy->last_name;

        return (new MailMessage)
            ->subject('GRN/RGA Approval Requested')
            ->greeting('Dear ' . $notifiable->first_name . ' ' . $notifiable->last_name)
            ->line('GRN form (' . $this->request->sn . ') by ' . $author
                . ' requires your attention')
            ->action('Click here to action the request', $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
