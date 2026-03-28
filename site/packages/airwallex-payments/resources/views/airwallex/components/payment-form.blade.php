<div x-data="{
    mode: @js($mode),
    callbackUrl: @js($this->callbackUrl),
    cancelUrl: @js($this->returnUrl ?: url()->current()),
    intentId: @js($this->intentId),
    currency: @js($this->cart->currency->code ?? null),
    countryCode: @js($this->cart->shippingAddress?->country?->iso2 ?? null),
    clientSecret: @js($this->clientSecret),
    error: null,
    sdkReady: false,
    loading: false,
    dropIn: null,
    payments: null,
    initEmbeddedEnv: @js(config('lunar.airwallex.env', 'demo')),
    sdkEnv() {
        const value = String(this.initEmbeddedEnv || 'demo').toLowerCase();
        if (value === 'sandbox') {
            return 'demo';
        }

        return value === 'prod' ? 'prod' : 'demo';
    },
    async initSdk() {
        if (!window.AirwallexComponentsSDK) {
            throw new Error('Airwallex SDK is not loaded.');
        }

        if (this.payments) {
            return this.payments;
        }

        const result = await window.AirwallexComponentsSDK.init({
            env: this.sdkEnv(),
            origin: window.location.origin,
            enabledElements: ['payments'],
        });

        this.payments = result?.payments || null;

        return this.payments;
    },
    async initEmbedded() {
        if (this.mode !== 'embedded') {
            return;
        }

        this.loading = true;

        try {
            if (!window.AirwallexComponentsSDK || !this.intentId || !this.clientSecret) {
                const missing = [];
                if (!window.AirwallexComponentsSDK) missing.push('sdk');
                if (!this.intentId) missing.push('intent_id');
                if (!this.clientSecret) missing.push('client_secret');
                this.error = 'Embedded checkout is currently unavailable (' + missing.join(', ') + '). You can continue with redirect checkout.';
                return;
            }

            await this.initSdk();

            await this.$nextTick();

            const target = document.getElementById('airwallex-dropin');
            if (!target) {
                return;
            }

            this.dropIn = await window.AirwallexComponentsSDK.createElement('dropIn', {
                intent_id: this.intentId,
                client_secret: this.clientSecret,
                currency: this.currency || undefined,
            });

            if (!this.dropIn) {
                return;
            }

            this.dropIn.mount('airwallex-dropin');
            this.sdkReady = true;

            this.dropIn.on?.('success', () => {
                window.location.href = this.callbackUrl;
            });

            this.dropIn.on?.('error', (event) => {
                console.warn('Airwallex embedded error', event);
            });

            target.addEventListener('onSuccess', () => {
                window.location.href = this.callbackUrl;
            });

            target.addEventListener('onError', (event) => {
                console.warn('Airwallex embedded onError event', event);
                this.sdkReady = false;
            });
        } catch (e) {
            this.error = e?.message || 'Unable to initialize Airwallex embedded checkout.';
            this.sdkReady = false;
        } finally {
            this.loading = false;
        }
    },
    async startRedirect() {
        this.loading = true;
        this.error = null;

        try {
            if (!this.intentId || !this.clientSecret) {
                throw new Error('Redirect checkout is unavailable because the payment intent is incomplete.');
            }

            const payments = await this.initSdk();

            if (!payments?.redirectToCheckout) {
                throw new Error('Hosted redirect checkout is unavailable in the loaded Airwallex SDK.');
            }

            payments.redirectToCheckout({
                intent_id: this.intentId,
                client_secret: this.clientSecret,
                currency: this.currency || undefined,
                country_code: this.countryCode || undefined,
                successUrl: this.callbackUrl,
                cancelUrl: this.cancelUrl,
            });
        } catch (e) {
            this.error = e?.message || 'Unable to start Airwallex redirect checkout.';
        } finally {
            this.loading = false;
        }
    },
}"
x-init="initEmbedded()">
    <div x-show="mode === 'embedded'">
        <div id="airwallex-dropin" class="min-h-[220px]"></div>

        <div class="mt-4 text-sm text-gray-600" x-show="!intentId || !clientSecret">
            Embedded checkout is unavailable for this cart. You can continue with redirect checkout below.
        </div>
    </div>

    <div class="mt-4" x-show="mode === 'redirect' || !intentId || !clientSecret || !sdkReady || loading || error">
        <button class="inline-flex items-center px-5 py-3 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-900 disabled:opacity-50"
                type="button"
                x-bind:disabled="loading || !intentId || !clientSecret"
                x-on:click.prevent="startRedirect()">
            Pay with Airwallex
        </button>

        <div class="mt-4 text-sm text-red-600" x-show="error" x-text="error"></div>
    </div>

</div>
