<?php

namespace App\Observers;

use App\Mail\OrderShipped;
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
        $placedNow = $order->wasChanged('placed_at') && ! empty($order->placed_at);

        if (! $placedNow) {
            return;
        }

        $orderMeta = (array) ($order->meta ?? []);

        if ((bool) data_get($orderMeta, 'inventory.stock_reduced', false)) {
            return;
        }

        $order->loadMissing('lines.purchasable');

        foreach ($order->lines as $line) {
            $purchasable = $line->purchasable;
            $quantity = (int) $line->quantity;
            $stockAllocated = 0;
            $backorderAllocated = 0;

            // Skip if no purchasable or if purchasable is always available
            if (! $purchasable || $purchasable->purchasable === 'always') {
                continue;
            }

            if ($purchasable->purchasable === 'in_stock') {
                // For 'in stock' tier, just reduce stock
                $stockAvailable = max(0, (int) $purchasable->stock);
                $stockAllocated = min($stockAvailable, $quantity);

                if ($stockAllocated > 0) {
                    $purchasable->decrement('stock', $stockAllocated);
                }
            } elseif ($purchasable->purchasable === 'in_stock_or_on_backorder') {
                // For 'in stock or backorder' tier, reduce stock first, then backorder if needed
                $stockAvailable = max(0, (int) $purchasable->stock);
                $stockAllocated = min($stockAvailable, $quantity);

                if ($stockAllocated > 0) {
                    $purchasable->decrement('stock', $stockAllocated);
                }

                $remaining = $quantity - $stockAllocated;

                if ($remaining > 0) {
                    $backorderAvailable = max(0, (int) $purchasable->backorder);
                    $backorderAllocated = min($backorderAvailable, $remaining);

                    if ($backorderAllocated > 0) {
                        $purchasable->decrement('backorder', $backorderAllocated);
                    }
                }
            }

            $lineMeta = (array) ($line->meta ?? []);
            $lineMeta['inventory'] = array_merge((array) ($lineMeta['inventory'] ?? []), [
                'stock_allocated' => $stockAllocated,
                'backorder_allocated' => $backorderAllocated,
                'is_backorder' => $backorderAllocated > 0,
            ]);

            $line->updateQuietly([
                'meta' => $lineMeta,
            ]);
        }

        $orderMeta['inventory']['stock_reduced'] = true;
        $orderMeta['inventory']['stock_reduced_at'] = now()->toIso8601String();

        $order->updateQuietly([
            'meta' => $orderMeta,
        ]);
    }
}

