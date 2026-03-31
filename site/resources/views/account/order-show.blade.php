<x-layouts.storefront title="Order Details">
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'My Orders', 'url' => route('orders')],
            ['label' => $order->reference ?: ('#' . $order->id), 'url' => null],
        ]" />

        <section class="bg-white border border-gray-100 rounded-xl">
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100">
                <h1 class="text-lg font-medium">Order {{ $order->reference ?: ('#' . $order->id) }}</h1>

                <a class="inline-flex items-center justify-center px-4 py-2 text-xs font-medium text-white bg-black border border-black rounded-lg hover:bg-gray-900"
                   href="{{ route('orders') }}">
                    Back to Orders
                </a>
            </div>

            @include('account._tabs', ['activeTab' => $activeTab ?? 'orders'])

            <div class="p-6 space-y-6">
                @php
                    $trackingUrl = \App\Support\TrackingLinkHelper::from($order->tracking_company, $order->shipping_tracking_number);
                    $trackingCompanyLabel = \App\Support\TrackingLinkHelper::labelFrom($order->tracking_company);
                @endphp

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="p-4 border border-gray-100 rounded-lg">
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Reference</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $order->reference ?: ('#' . $order->id) }}</p>
                    </div>

                    <div class="p-4 border border-gray-100 rounded-lg">
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Status</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ ucfirst((string) $order->status) }}</p>
                    </div>

                    <div class="p-4 border border-gray-100 rounded-lg">
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Placed</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ optional($order->placed_at)->format('j M Y, g:i a') ?? '-' }}</p>
                    </div>

                    <div class="p-4 border border-gray-100 rounded-lg">
                        <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Total</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ $order->total->formatted }}</p>
                    </div>
                </div>

                @if ($order->shipping_tracking_number || $order->tracking_company || $order->shipped_at)
                    <section class="p-4 border border-gray-100 rounded-lg">
                        <h2 class="text-sm font-semibold text-gray-900">Shipping Tracking</h2>

                        <div class="grid grid-cols-1 gap-3 mt-3 text-sm sm:grid-cols-3">
                            <div>
                                <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Tracking Number</p>
                                <p class="mt-1 text-gray-900">{{ $order->shipping_tracking_number ?: '-' }}</p>
                            </div>

                            <div>
                                <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Company</p>
                                <p class="mt-1 text-gray-900">{{ $trackingCompanyLabel ?: '-' }}</p>
                            </div>

                            <div>
                                <p class="text-xs font-medium tracking-wide text-gray-500 uppercase">Shipped At</p>
                                <p class="mt-1 text-gray-900">{{ $order->shipped_at ? \Carbon\Carbon::parse($order->shipped_at)->format('j M Y, g:i a') : '-' }}</p>
                            </div>
                        </div>

                        @if ($trackingUrl)
                            <div class="mt-4">
                                <a class="inline-flex items-center justify-center px-4 py-2 text-xs font-medium text-white bg-black border border-black rounded-lg hover:bg-gray-900"
                                   href="{{ $trackingUrl }}"
                                   target="_blank"
                                   rel="noopener noreferrer">
                                    Track Package
                                </a>
                            </div>
                        @endif
                    </section>
                @endif

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <section class="p-4 border border-gray-100 rounded-lg">
                        <h2 class="text-sm font-semibold text-gray-900">Shipping Address</h2>
                        <div class="mt-3 text-sm text-gray-700">
                            @if ($order->shippingAddress)
                                <p>{{ trim(($order->shippingAddress->first_name ?? '') . ' ' . ($order->shippingAddress->last_name ?? '')) }}</p>
                                @if ($order->shippingAddress->line_one)
                                    <p>{{ $order->shippingAddress->line_one }}</p>
                                @endif
                                @if ($order->shippingAddress->line_two)
                                    <p>{{ $order->shippingAddress->line_two }}</p>
                                @endif
                                @if ($order->shippingAddress->line_three)
                                    <p>{{ $order->shippingAddress->line_three }}</p>
                                @endif
                                <p>
                                    {{ $order->shippingAddress->city }}
                                    {{ $order->shippingAddress->state }}
                                    {{ $order->shippingAddress->postcode }}
                                </p>
                                @if ($order->shippingAddress->contact_email)
                                    <p>{{ $order->shippingAddress->contact_email }}</p>
                                @endif
                            @else
                                <p>-</p>
                            @endif
                        </div>
                    </section>

                    <section class="p-4 border border-gray-100 rounded-lg">
                        <h2 class="text-sm font-semibold text-gray-900">Billing Address</h2>
                        <div class="mt-3 text-sm text-gray-700">
                            @if ($order->billingAddress)
                                <p>{{ trim(($order->billingAddress->first_name ?? '') . ' ' . ($order->billingAddress->last_name ?? '')) }}</p>
                                @if ($order->billingAddress->line_one)
                                    <p>{{ $order->billingAddress->line_one }}</p>
                                @endif
                                @if ($order->billingAddress->line_two)
                                    <p>{{ $order->billingAddress->line_two }}</p>
                                @endif
                                @if ($order->billingAddress->line_three)
                                    <p>{{ $order->billingAddress->line_three }}</p>
                                @endif
                                <p>
                                    {{ $order->billingAddress->city }}
                                    {{ $order->billingAddress->state }}
                                    {{ $order->billingAddress->postcode }}
                                </p>
                                @if ($order->billingAddress->contact_email)
                                    <p>{{ $order->billingAddress->contact_email }}</p>
                                @endif
                            @else
                                <p>-</p>
                            @endif
                        </div>
                    </section>
                </div>

                <section class="border border-gray-100 rounded-lg">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Order Items</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 border-b border-gray-100">
                                    <th class="px-4 py-3 font-medium">Item</th>
                                    <th class="px-4 py-3 font-medium text-right">Qty</th>
                                    <th class="px-4 py-3 font-medium text-right">Unit</th>
                                    <th class="px-4 py-3 font-medium text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($order->lines as $line)
                                    <tr class="border-b border-gray-100 last:border-b-0">
                                        <td class="px-4 py-3 text-gray-900">{{ $line->description ?: ('Line #' . $line->id) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ (int) $line->quantity }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $line->unit_price->formatted }}</td>
                                        <td class="px-4 py-3 text-right text-gray-900">{{ $line->sub_total->formatted }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-4 py-4 text-gray-600" colspan="4">No line items available for this order.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-4 border-t border-gray-100">
                        <div class="w-full ml-auto space-y-2 sm:max-w-sm">
                            <div class="flex items-center justify-between text-sm text-gray-700">
                                <span>Subtotal</span>
                                <span>{{ $order->sub_total->formatted }}</span>
                            </div>

                            <div class="flex items-center justify-between text-sm text-gray-700">
                                <span>Discount</span>
                                <span>
                                    @if (($order->discount_total->value ?? 0) > 0)
                                        -{{ $order->discount_total->formatted }}
                                    @else
                                        {{ $order->discount_total->formatted }}
                                    @endif
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-sm text-gray-700">
                                <span>Shipping</span>
                                <span>{{ $order->shipping_total->formatted }}</span>
                            </div>

                            <div class="flex items-center justify-between text-sm text-gray-700">
                                <span>Tax</span>
                                <span>{{ $order->tax_total->formatted }}</span>
                            </div>

                            <div class="flex items-center justify-between pt-2 text-sm font-semibold text-gray-900 border-t border-gray-100">
                                <span>Total</span>
                                <span>{{ $order->total->formatted }}</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    </div>
</x-layouts.storefront>
