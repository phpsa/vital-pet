<div>
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Checkout', 'url' => null],
        ]" />

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 lg:items-start">
            <div class="px-6 py-8 space-y-4 bg-white border border-gray-100 lg:sticky lg:top-8 rounded-xl lg:order-last">
                <h3 class="font-medium">
                    Order Summary
                </h3>

                <div class="flow-root">
                    <div class="-my-4 divide-y divide-gray-100">
                        @foreach ($cart->lines as $line)
                            <div class="flex items-center py-4"
                                 wire:key="cart_line_{{ $line->id }}">
                                <img class="object-cover w-16 h-16 rounded"
                                     src="{{ $line->purchasable->getThumbnail()->getUrl() }}" />

                                <div class="flex-1 ml-4">
                                    <p class="text-sm font-medium max-w-[35ch]">
                                        {{ $line->purchasable->getDescription() }}
                                        @if ($this->isLineOnBackorder($line))
                                            <span class="inline-block ml-2 px-2 py-0.5 text-xs font-semibold text-amber-800 bg-amber-100 rounded-full">
                                                Backorder
                                            </span>
                                        @endif
                                    </p>

                                    <span class="block mt-1 text-xs text-gray-500">
                                        {{ $line->quantity }} @ {{ $line->subTotal->formatted() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flow-root">
                    <dl class="-my-4 text-sm divide-y divide-gray-100">
                        <div class="flex flex-wrap py-4">
                            <dt class="w-1/2 font-medium">
                                Sub Total
                            </dt>

                            <dd class="w-1/2 text-right">
                                {{ $cart->subTotal->formatted() }}
                            </dd>
                        </div>

                        @if ($this->shippingOption)
                            <div class="flex flex-wrap py-4">
                                <dt class="w-1/2 font-medium">
                                    {{ $this->shippingOption->getName() ?: $this->shippingOption->getDescription() }}
                                </dt>

                                <dd class="w-1/2 text-right">
                                    {{ $this->shippingOption->getPrice()->formatted() }}
                                </dd>
                            </div>
                        @endif

                        @foreach ($cart->taxBreakdown->amounts as $tax)
                            <div class="flex flex-wrap py-4">
                                <dt class="w-1/2 font-medium">
                                    {{ $tax->description }}
                                </dt>

                                <dd class="w-1/2 text-right">
                                    {{ $tax->price->formatted() }}
                                </dd>
                            </div>
                        @endforeach

                        <div class="flex flex-wrap py-4">
                            <dt class="w-1/2 font-medium">
                                Total
                            </dt>

                            <dd class="w-1/2 text-right">
                                {{ $cart->total->formatted() }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-2">
                @guest
                    <section class="bg-white border border-gray-100 rounded-xl">
                        <div class="flex items-center h-16 px-6 border-b border-gray-100">
                            <h3 class="text-lg font-medium">Checkout Options</h3>
                        </div>

                        <div class="p-6">
                            <p class="text-sm text-gray-600">
                                You can sign in to use saved addresses and faster checkout, create a new account,
                                or continue as a guest.
                            </p>

                            <div class="ves-checkout-guest-actions mt-6 justify-end">
                                <a class="px-5 py-3 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-900"
                                   href="{{ route('login', ['redirect_to' => 'checkout']) }}">
                                    Login & Return to Checkout
                                </a>

                                @if (! config('template.storefront_requires_auth'))
                                    <a class="px-5 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50"
                                       href="{{ route('register', ['redirect_to' => 'checkout']) }}">
                                        Register
                                    </a>
                                @endif
                            </div>
                        </div>
                    </section>
                @endguest

                @include('partials.checkout.address', [
                    'type' => 'shipping',
                    'step' => $steps['shipping_address'],
                ])

                @include('partials.checkout.shipping_option', [
                    'step' => $steps['shipping_option'],
                ])

                @include('partials.checkout.payment', [
                    'step' => $steps['payment'],
                ])
            </div>
        </div>
    </div>
</div>
