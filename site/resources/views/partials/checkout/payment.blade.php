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

            @if ($this->isPaypalEnabled)
                <div x-data="{
                    loading: false,
                    error: null,
                    async pay() {
                        this.loading = true;
                        this.error = null;
                        try {
                            const response = await fetch('/api/paypal/order', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                                    'Accept': 'application/json',
                                },
                            });
                            const data = await response.json();
                            if (!response.ok) {
                                this.error = data.message || 'Could not create PayPal order. Please try again.';
                                return;
                            }
                            // PayPal returns error objects with a 'name' field (e.g. INVALID_CLIENT)
                            if (data.name && !data.links) {
                                this.error = data.message || `PayPal error: ${data.name}`;
                                console.error('PayPal order error', data);
                                return;
                            }
                            const link = (data.links || []).find(l => l.rel === 'payer-action');
                            if (!link?.href) {
                                this.error = 'PayPal approval URL not found. Please try again.';
                                console.error('PayPal order response (no payer-action link)', data);
                                return;
                            }
                            window.location.href = link.href;
                        } catch (e) {
                            this.error = 'An error occurred. Please try again.';
                            console.error('PayPal fetch error', e);
                        } finally {
                            this.loading = false;
                        }
                    }
                }">
                    <div x-show="error" class="p-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg" x-text="error"></div>

                    <button type="button"
                            x-on:click="pay()"
                            x-bind:disabled="loading"
                            class="inline-flex items-center gap-2 px-5 py-3 text-sm font-medium text-white bg-[#003087] rounded-lg hover:bg-[#002069] disabled:opacity-50">
                        <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!loading" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.59 3.025-2.566 6.357-8.993 6.357h-2.19c-1.044 0-1.932.765-2.092 1.796L6.18 21.337H2.47l.016-.1 2.94-18.61c.08-.52.527-.9 1.05-.9h7.46c1.96 0 3.39.361 4.38 1.072.938.674 1.518 1.666 1.906 3.118z"/>
                        </svg>
                        {{ $this->showPaymentGatewayLabels ? 'Pay with PayPal' : 'Pay with PayPal' }}
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>
