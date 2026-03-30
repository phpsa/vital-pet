<?php

namespace App\Listeners;

use App\Mail\AdminOrderReceivedMail;
use App\Mail\BuyerOrderReceivedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Models\Order;

class SendOrderReceivedEmails implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PaymentAttemptEvent $event): void
    {
        $payment = $event->paymentAuthorize;

        if (! $payment->success || ! $payment->orderId) {
            return;
        }

        $order = Order::query()
            ->with(['billingAddress', 'shippingAddress', 'lines'])
            ->find($payment->orderId);

        if (! $order) {
            return;
        }

        $meta = (array) $order->meta;
        $emailsMeta = (array) ($meta['emails'] ?? []);

        if (($emailsMeta['order_received_sent'] ?? false) === true) {
            return;
        }

        $adminEmail = (string) config('services.store.admin_email', '');
        $buyerEmail = (string) (
            $order->billingAddress?->contact_email
            ?: $order->shippingAddress?->contact_email
            ?: ''
        );

        $adminSent = false;
        $buyerSent = false;

        if ($adminEmail !== '') {
            Mail::to($adminEmail)->queue(new AdminOrderReceivedMail($order));
            $adminSent = true;
        }

        if ($buyerEmail !== '') {
            Mail::to($buyerEmail)->queue(new BuyerOrderReceivedMail($order));
            $buyerSent = true;
        }

        if (! $adminSent && ! $buyerSent) {
            Log::warning('Order received emails skipped: no recipients available', [
                'order_id' => $order->id,
            ]);

            return;
        }

        $order->meta = array_merge($meta, [
            'emails' => array_merge($emailsMeta, [
                'order_received_sent' => true,
                'order_received_sent_at' => now()->toIso8601String(),
                'order_received_admin_sent' => $adminSent,
                'order_received_buyer_sent' => $buyerSent,
                'order_received_admin_email' => $adminSent ? $adminEmail : null,
                'order_received_buyer_email' => $buyerSent ? $buyerEmail : null,
            ]),
        ]);

        $order->save();
    }
}
