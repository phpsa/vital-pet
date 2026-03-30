<div class="bg-white border border-gray-100 rounded-xl">
    <div class="flex items-center h-16 px-6 border-b border-gray-100">
        <h3 class="text-lg font-medium">
            Payment
        </h3>
    </div>

    @if ($currentStep >= $step)
        <div class="p-6 space-y-4">
            @if ($paymentError)
                <div class="p-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg">
                    {{ $paymentError }}
                </div>
            @endif

            @if ($this->isAirwallexEnabled)
                <livewire:airwallex.payment :cart="$cart"
                                            :returnUrl="route('checkout.view')"
                                            :buttonLabel="$this->showPaymentGatewayLabels ? 'Pay by card (Airwallex)' : 'Pay by card'" />
            @endif

            @if ($this->isSendingGatewayEnabled)
                @php($packet = $this->sendingGatewayPacket)

                @if ($packet)
                    <form method="POST"
                          action="{{ $packet['url'] }}"
                          class="space-y-3">
                        <input type="hidden"
                               name="payload"
                               value="{{ $packet['payload'] }}">

                        <button class="inline-flex items-center px-5 py-3 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-900"
                                type="submit">
                            {{ $this->showPaymentGatewayLabels ? 'Pay by card (Redirect)' : 'Pay by card' }}
                        </button>
                    </form>
                @endif
            @endif
        </div>
    @endif
</div>
