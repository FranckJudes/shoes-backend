<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentReceivedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        $order = $payment->order;
        $user = $order->user;
        
        $user->notify(new PaymentReceivedNotification($payment));
    }
}