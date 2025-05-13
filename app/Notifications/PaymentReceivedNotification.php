<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The payment instance.
     *
     * @var \App\Models\Payment
     */
    protected $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/orders/' . $this->payment->order_id);

        return (new MailMessage)
            ->subject('Payment Confirmation - Order #' . $this->payment->order_id)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payment has been received and confirmed.')
            ->line('Order ID: #' . $this->payment->order_id)
            ->line('Amount: $' . number_format($this->payment->amount, 2))
            ->line('Payment Method: ' . ucfirst($this->payment->payment_method))
            ->line('Transaction ID: ' . $this->payment->transaction_id)
            ->action('View Order', $url)
            ->line('Thank you for your purchase!');
    }
}