<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Lunar\Models\Order;

class BuyerOrderReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function build(): self
    {
        $reference = $this->order->reference ?: ('#'.$this->order->id);
        $adminEmail = (string) config('services.store.admin_email', '');

        $mail = $this
            ->subject('Order confirmation '.$reference)
            ->view('emails.order-received-buyer', [
                'order' => $this->order,
            ]);

        if ($adminEmail !== '') {
            $mail->replyTo($adminEmail);
        }

        return $mail;
    }
}
