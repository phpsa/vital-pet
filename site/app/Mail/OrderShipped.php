<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Lunar\Models\Order;

final class OrderShipped extends Mailable implements ShouldQueue
{
    use Queueable;

    public Order $order;

    public function __construct(int $orderId)
    {
        $this->order = Order::findOrFail($orderId);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Shipped - Order #'.$this->order->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-shipped',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
