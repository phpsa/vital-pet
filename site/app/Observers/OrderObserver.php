<?php

namespace App\Observers;

use App\Mail\OrderShipped;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Mail;
use Lunar\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        // Stock is reduced when the order is actually placed (placed_at), not on initial draft creation.
    }

    public function updated(Order $order): void
    {
        $this->reduceStockWhenPlaced($order);

        $this->markAsDispatchedWhenTrackingAdded($order);

        if (! $this->shouldSendShippedEmail($order)) {
            return;
        }

        $email = $this->resolveOrderEmail($order);

        if (! $email) {
            return;
        }

        if (! $order->shipped_at) {
            $order->updateQuietly(['shipped_at' => now()]);
        }

        Mail::to($email)->queue(new OrderShipped($order->id));

        $meta = (array) ($order->meta ?? []);
        $meta['emails_sent']['order_shipped'] = now()->toDateTimeString();

        $order->updateQuietly(['meta' => $meta]);
    }

    protected function markAsDispatchedWhenTrackingAdded(Order $order): void
    {
        $trackingWasAdded = $order->wasChanged('shipping_tracking_number')
            && ! empty($order->shipping_tracking_number);

        if (! $trackingWasAdded) {
            return;
        }

        if ($order->status === 'dispatched' || $order->status === 'cancelled') {
            return;
        }

        $order->updateQuietly([
            'status' => 'dispatched',
        ]);
    }

    protected function shouldSendShippedEmail(Order $order): bool
    {
        $trackingChanged = $order->wasChanged('shipping_tracking_number');
        $companyChanged = $order->wasChanged('tracking_company');
        $shippingDetailsChanged = $trackingChanged || $companyChanged;
        $hasTracking = ! empty($order->shipping_tracking_number);

        $meta = (array) ($order->meta ?? []);
        $alreadySent = isset($meta['emails_sent']['order_shipped']);

        return $shippingDetailsChanged && $hasTracking && ! $alreadySent;
    }

    protected function resolveOrderEmail(Order $order): ?string
    {
        $order->loadMissing(['billingAddress', 'shippingAddress', 'user']);

        return $order->billingAddress?->contact_email
            ?: $order->shippingAddress?->contact_email
            ?: $order->user?->email;
    }

    protected function reduceStockWhenPlaced(Order $order): void
    {
        app(InventoryService::class)->reduceStockForPlacedOrder($order);
    }
}

