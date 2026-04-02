<?php

namespace App\Services;

use App\Models\ProductVariantRegionalStock;
use App\Support\StorefrontCountry;
use Illuminate\Database\Eloquent\Model;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Models\ProductVariant;

class InventoryService
{
    public function resolveCountryIdForCart(?Cart $cart): ?int
    {
        // 1. Prefer the cart's saved shipping address country
        $cartCountryId = $cart?->shippingAddress?->country_id;

        if ($cartCountryId) {
            return $cartCountryId;
        }

        // 2. Use the active storefront session country (set by middleware / switcher)
        return StorefrontCountry::id();
    }

    public function availableQuantityForPurchasable($purchasable, ?int $countryId = null): int
    {
        if (! $purchasable) {
            return 0;
        }

        if ($purchasable->purchasable === 'always') {
            return PHP_INT_MAX;
        }

        $globalStock = max(0, (int) $purchasable->stock);
        $globalBackorder = max(0, (int) $purchasable->backorder);

        if ($purchasable->purchasable === 'in_stock') {
            return $this->inStockQuantityForPurchasable($purchasable, $countryId);
        }

        if ($purchasable->purchasable === 'in_stock_or_on_backorder') {
            if ($this->regionalStockEnabled($purchasable)) {
                if (! $countryId) {
                    return 0;
                }

                $regional = $this->regionalRow($purchasable, $countryId);

                return $regional ? max(0, (int) $regional->stock) + max(0, (int) $regional->backorder) : 0;
            }

            return $globalStock + $globalBackorder;
        }

        return 0;
    }

    public function inStockQuantityForPurchasable($purchasable, ?int $countryId = null): int
    {
        if (! $purchasable) {
            return 0;
        }

        if ($purchasable->purchasable === 'always') {
            return PHP_INT_MAX;
        }

        $globalStock = max(0, (int) $purchasable->stock);

        if ($this->regionalStockEnabled($purchasable)) {
            if (! $countryId) {
                return 0;
            }

            $regional = $this->regionalRow($purchasable, $countryId);

            return $regional ? max(0, (int) $regional->stock) : 0;
        }

        return $globalStock;
    }

    public function validateRequestedQuantity($purchasable, int $requestedQuantity, ?int $countryId = null): array
    {
        $available = $this->availableQuantityForPurchasable($purchasable, $countryId);

        if ($requestedQuantity <= $available) {
            return [
                'ok' => true,
                'available' => $available,
            ];
        }

        return [
            'ok' => false,
            'available' => $available,
        ];
    }

    public function stockStatusForPurchasable($purchasable, ?int $countryId = null): array
    {
        if (! $purchasable) {
            return [
                'text' => null,
                'tone' => 'danger',
            ];
        }

        if ($purchasable->purchasable === 'always') {
            return [
                'text' => '5+ in Stock',
                'tone' => 'success',
            ];
        }

        $available = $this->availableQuantityForPurchasable($purchasable, $countryId);

        if ($available <= 0) {
            return [
                'text' => 'Out of Stock',
                'tone' => 'danger',
            ];
        }

        $text = $available >= 5 ? '5+ in Stock' : "{$available} in Stock";

        if ($purchasable->purchasable === 'in_stock_or_on_backorder') {
            $tone = $this->hasAnyBackorderCapacity($purchasable, $countryId) ? 'warning' : 'success';

            return [
                'text' => $text,
                'tone' => $tone,
            ];
        }

        return [
            'text' => $text,
            'tone' => 'success',
        ];
    }

    public function requestedByPurchasable(iterable $lines): array
    {
        $requestedByPurchasable = [];

        foreach ($lines as $line) {
            if (is_array($line)) {
                $purchasableType = (string) ($line['purchasable_type'] ?? '');
                $purchasableId = (int) ($line['purchasable_id'] ?? 0);
                $quantity = (int) ($line['quantity'] ?? 0);
            } else {
                $purchasableType = (string) $line->purchasable_type;
                $purchasableId = (int) $line->purchasable_id;
                $quantity = (int) $line->quantity;
            }

            if ($purchasableType === '' || $purchasableId <= 0) {
                continue;
            }

            $key = $purchasableType.':'.$purchasableId;
            $requestedByPurchasable[$key] = ($requestedByPurchasable[$key] ?? 0) + $quantity;
        }

        return $requestedByPurchasable;
    }

    public function reduceStockForPlacedOrder(Order $order): void
    {
        $placedNow = $order->wasChanged('placed_at') && ! empty($order->placed_at);

        if (! $placedNow) {
            return;
        }

        $orderMeta = (array) ($order->meta ?? []);

        if ((bool) data_get($orderMeta, 'inventory.stock_reduced', false)) {
            return;
        }

        $order->loadMissing('shippingAddress.country');

        $countryId = $order->shippingAddress?->country_id;
        $stockLines = $order->lines
            ->filter(fn ($line) => $this->isStockManagedLine($line))
            ->values();

        $stockLines->loadMissing('purchasable');

        foreach ($stockLines as $line) {
            $purchasable = $line->purchasable;
            $requestedQuantity = (int) $line->quantity;

            if (! $purchasable || $purchasable->purchasable === 'always') {
                continue;
            }

            $remaining = $requestedQuantity;
            $regionalStockAllocated = 0;
            $globalStockAllocated = 0;
            $regionalBackorderAllocated = 0;
            $globalBackorderAllocated = 0;

            $regional = null;
            $regionalEnabled = $this->regionalStockEnabled($purchasable);

            if ($countryId && $regionalEnabled) {
                $regional = $this->regionalRow($purchasable, $countryId);
            }

            if ($remaining > 0 && $regional) {
                $allocate = min(max(0, (int) $regional->stock), $remaining);

                if ($allocate > 0) {
                    $regional->decrement('stock', $allocate);
                    $regionalStockAllocated += $allocate;
                    $remaining -= $allocate;
                }
            }

            if ($remaining > 0 && ! $regionalEnabled) {
                $allocate = min(max(0, (int) $purchasable->stock), $remaining);

                if ($allocate > 0) {
                    $purchasable->decrement('stock', $allocate);
                    $globalStockAllocated += $allocate;
                    $remaining -= $allocate;
                }
            }

            if ($remaining > 0 && $purchasable->purchasable === 'in_stock_or_on_backorder') {
                if ($regional) {
                    $allocate = min(max(0, (int) $regional->backorder), $remaining);

                    if ($allocate > 0) {
                        $regional->decrement('backorder', $allocate);
                        $regionalBackorderAllocated += $allocate;
                        $remaining -= $allocate;
                    }
                }

                if ($remaining > 0 && ! $regionalEnabled) {
                    $allocate = min(max(0, (int) $purchasable->backorder), $remaining);

                    if ($allocate > 0) {
                        $purchasable->decrement('backorder', $allocate);
                        $globalBackorderAllocated += $allocate;
                        $remaining -= $allocate;
                    }
                }
            }

            $stockAllocated = $regionalStockAllocated + $globalStockAllocated;
            $backorderAllocated = $regionalBackorderAllocated + $globalBackorderAllocated;

            $lineMeta = (array) ($line->meta ?? []);
            $lineMeta['inventory'] = array_merge((array) ($lineMeta['inventory'] ?? []), [
                'stock_allocated' => $stockAllocated,
                'backorder_allocated' => $backorderAllocated,
                'regional_stock_allocated' => $regionalStockAllocated,
                'global_stock_allocated' => $globalStockAllocated,
                'regional_backorder_allocated' => $regionalBackorderAllocated,
                'global_backorder_allocated' => $globalBackorderAllocated,
                'is_backorder' => $backorderAllocated > 0,
                'country_id' => $countryId,
            ]);

            $line->updateQuietly([
                'meta' => $lineMeta,
            ]);
        }

        $orderMeta['inventory']['stock_reduced'] = true;
        $orderMeta['inventory']['stock_reduced_at'] = now()->toIso8601String();
        $orderMeta['inventory']['country_id'] = $countryId;

        $order->updateQuietly([
            'meta' => $orderMeta,
        ]);
    }

    protected function regionalStockEnabled($purchasable): bool
    {
        return (bool) ($purchasable->regional_stock_enabled ?? false);
    }

    protected function regionalRow($purchasable, int $countryId): ?ProductVariantRegionalStock
    {
        if (! $purchasable instanceof ProductVariant) {
            return null;
        }

        return ProductVariantRegionalStock::query()
            ->where('product_variant_id', $purchasable->id)
            ->where('country_id', $countryId)
            ->first();
    }

    protected function hasAnyBackorderCapacity($purchasable, ?int $countryId = null): bool
    {
        $globalBackorder = max(0, (int) $purchasable->backorder);

        if (! $this->regionalStockEnabled($purchasable)) {
            return $globalBackorder > 0;
        }

        if (! $countryId) {
            return false;
        }

        $regional = $this->regionalRow($purchasable, $countryId);

        if (! $regional) {
            return false;
        }

        return max(0, (int) $regional->backorder) > 0;
    }

    protected function isStockManagedLine($line): bool
    {
        $type = (string) ($line->purchasable_type ?? '');

        return $type !== '' && is_subclass_of($type, Model::class);
    }
}
