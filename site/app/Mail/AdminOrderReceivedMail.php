<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Lunar\Models\Order;

class AdminOrderReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function build(): self
    {
        $reference = $this->order->reference ?: ('#'.$this->order->id);
        $customerEmail = $this->order->billingAddress?->contact_email ?: $this->order->shippingAddress?->contact_email;

        $mail = $this
            ->subject('New order received '.$reference)
            ->view('emails.order-received-admin', [
                'order' => $this->order,
            ]);

        if ($customerEmail) {
            $mail->replyTo($customerEmail);
        }

        return $mail;
    }
}
