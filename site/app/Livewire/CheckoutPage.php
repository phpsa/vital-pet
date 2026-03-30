<?php

namespace App\Livewire;

use App\Support\LandingSignature;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Facades\Payments;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;

class CheckoutPage extends Component
{
    /**
     * The Cart instance.
     */
    public ?Cart $cart;

    /**
     * The shipping address instance.
     */
    public ?CartAddress $shipping = null;

    /**
     * The billing address instance.
     */
    public ?CartAddress $billing = null;

    /**
     * The current checkout step.
     */
    public int $currentStep = 1;

    /**
     * Whether the shipping address is the billing address too.
     */
    public bool $shippingIsBilling = true;

    /**
     * The chosen shipping option.
     */
    public $chosenShipping = null;

    /**
     * The checkout steps.
     */
    public array $steps = [
        'shipping_address' => 1,
        'shipping_option' => 2,
        'billing_address' => 3,
        'payment' => 4,
    ];

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'cartUpdated' => 'refreshCart',
        'selectedShippingOption' => 'refreshCart',
    ];

    public $payment_intent = null;

    public string $paymentError = '';

    protected $queryString = [
        'payment_intent',
    ];

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return array_merge(
            $this->getAddressValidation('shipping'),
            $this->getAddressValidation('billing'),
            [
                'chosenShipping' => 'required',
            ]
        );
    }

    public function mount(): void
    {
        $this->shippingIsBilling = true;

        if (! $this->cart = CartSession::current()) {
            $this->redirect('/');

            return;
        }

        if ($this->payment_intent) {
            $payment = Payments::driver('airwallex')->cart($this->cart)->withData([
                'payment_intent' => $this->payment_intent,
            ])->authorize();

            if ($payment->success) {
                $orderId = $payment->orderId ?: $this->cart->completedOrder?->id;

                if (! $orderId) {
                    $this->redirectRoute('checkout.view');

                    return;
                }

                $this->redirect($this->signedSuccessUrl((int) $orderId));

                return;
            }

            $this->redirectRoute('checkout.view');

            return;
        }

        // Handle redirect gateway return (Airwallex via landing page)
        $gatewayReturnType = (string) request()->query('type', '');
        $gatewayIntentId = (string) request()->query('id', '');

        if ($gatewayReturnType === 'CANCEL_URL') {
            $this->paymentError = 'Payment was cancelled. Please try again.';
        } elseif ($gatewayReturnType === 'SUCCESS_URL' && $gatewayIntentId !== '') {
            $payment = Payments::driver('airwallex')->cart($this->cart)->withData([
                'payment_intent' => $gatewayIntentId,
            ])->authorize();

            if ($payment->success) {
                $orderId = $payment->orderId ?: $this->cart->completedOrder?->id;

                if (! $orderId) {
                    $this->redirectRoute('checkout.view');

                    return;
                }

                $this->redirect($this->signedSuccessUrl((int) $orderId));

                return;
            }

            $this->paymentError = $payment->message ?: 'Payment could not be confirmed. Please try again.';
        }

        // Do we have a shipping address?
        $this->shipping = $this->cart->shippingAddress ?: new CartAddress;

        $this->billing = $this->cart->billingAddress ?: new CartAddress;

        if ($defaultCountry = Country::firstWhere('iso3', 'AUS')) {
            if (! $this->shipping->country_id) {
                $this->shipping->country_id = $defaultCountry->id;
            }

            if (! $this->billing->country_id) {
                $this->billing->country_id = $defaultCountry->id;
            }
        }

        $this->determineCheckoutStep();
    }

    public function hydrate(): void
    {
        $this->shippingIsBilling = true;
        $this->cart = CartSession::current();
    }

    /**
     * Trigger an event to refresh addresses.
     */
    public function triggerAddressRefresh(): void
    {
        $this->dispatch('refreshAddress');
    }

    /**
     * Determines what checkout step we should be at.
     */
    public function determineCheckoutStep(): void
    {
        $shippingAddress = $this->cart->shippingAddress;
        $billingAddress = $this->cart->billingAddress;

        if ($shippingAddress) {
            if ($shippingAddress->id) {
                $this->currentStep = $this->steps['shipping_address'] + 1;
            }

            // Do we have a selected option?
            if ($this->shippingOption) {
                $this->chosenShipping = $this->shippingOption->getIdentifier();
                $this->currentStep = $this->steps['shipping_option'] + 1;
            } else {
                $this->currentStep = $this->steps['shipping_option'];
                $firstOption = $this->shippingOptions->first();
                $this->chosenShipping = $firstOption?->getIdentifier();
                $autoSelectedSingleOption = false;

                // Keep totals stable when there is only one shipping choice.
                if ($firstOption && $this->shippingOptions->count() === 1) {
                    CartSession::setShippingOption($firstOption);
                    $this->refreshCart();
                    $this->currentStep = $this->steps['shipping_option'] + 1;
                    $autoSelectedSingleOption = true;
                }

                if (! $autoSelectedSingleOption) {
                    return;
                }
            }
        }

        if ($billingAddress) {
            $this->currentStep = $this->steps['billing_address'] + 1;
        }
    }

    /**
     * Refresh the cart instance.
     */
    public function refreshCart(): void
    {
        $this->cart = $this->cart?->refresh()?->recalculate() ?: CartSession::current(calculate: true);
    }

    /**
     * Return the shipping option.
     */
    public function getShippingOptionProperty()
    {
        $shippingAddress = $this->cart->shippingAddress;

        if (! $shippingAddress) {
            return;
        }

        if ($option = $shippingAddress->shipping_option) {
            return ShippingManifest::getOptions($this->cart)->first(function ($opt) use ($option) {
                return $opt->getIdentifier() == $option;
            });
        }

        return null;
    }

    /**
     * Save the address for a given type.
     */
    public function saveAddress(string $type): void
    {
        $validatedData = $this->validate(
            $this->getAddressValidation($type)
        );

        $address = $this->{$type};

        if ($type == 'billing') {
            $this->cart->setBillingAddress($address);
            $this->billing = $this->cart->billingAddress;
        }

        if ($type == 'shipping') {
            $previousCountryId = $this->cart->shippingAddress?->country_id;

            $this->cart->setShippingAddress($address);
            $this->shipping = $this->cart->shippingAddress;

            // Billing always mirrors shipping.
            if ($billing = $this->cart->billingAddress) {
                $billing->fill($validatedData['shipping']);
                $this->cart->setBillingAddress($billing);
            } else {
                $address = $address->only(
                    $address->getFillable()
                );
                $this->cart->setBillingAddress($address);
            }

            $this->billing = $this->cart->billingAddress;

            if ($previousCountryId !== $this->shipping?->country_id) {
                $this->shipping->shipping_option = null;
                $this->shipping->save();
                $this->chosenShipping = null;
            }
        }

        $this->refreshCart();

        $this->determineCheckoutStep();
    }

    /**
     * Save the selected shipping option.
     */
    public function saveShippingOption(): void
    {
        $option = $this->shippingOptions->first(fn ($option) => $option->getIdentifier() == $this->chosenShipping);

        CartSession::setShippingOption($option);

        $this->refreshCart();

        $this->determineCheckoutStep();
    }

    public function checkout()
    {
        $payment = Payments::cart($this->cart)->withData([
            'payment_intent' => $this->payment_intent,
        ])->authorize();

        if ($payment->success) {
            $orderId = $payment->orderId ?: $this->cart->completedOrder?->id;

            if (! $orderId) {
                return $this->redirectRoute('checkout.view');
            }

            return $this->redirect($this->signedSuccessUrl((int) $orderId));
        }

        return $this->redirectRoute('checkout.view');
    }

    /**
     * Return the available countries.
     */
    public function getCountriesProperty(): Collection
    {
        return Country::whereIn('iso3', ['AUS', 'NZL'])->get();
    }

    /**
     * Return available shipping options.
     */
    public function getShippingOptionsProperty(): Collection
    {
        $options = ShippingManifest::getOptions(
            $this->cart
        );

        $shippingCountryIso3 = $this->shipping?->country?->iso3 ?: $this->cart?->shippingAddress?->country?->iso3;

        if (! $shippingCountryIso3) {
            return $options;
        }

        return $options->filter(function ($option) use ($shippingCountryIso3) {
            return strtoupper((string) $option->getIdentifier()) === strtoupper((string) $shippingCountryIso3);
        })->values();
    }

    /**
     * Return the address validation rules for a given type.
     */
    protected function getAddressValidation(string $type): array
    {
        return [
            "{$type}.first_name" => 'required',
            "{$type}.last_name" => 'required',
            "{$type}.line_one" => 'required',
            "{$type}.country_id" => 'required',
            "{$type}.city" => 'required',
            "{$type}.postcode" => 'required',
            "{$type}.company_name" => 'nullable',
            "{$type}.line_two" => 'nullable',
            "{$type}.line_three" => 'nullable',
            "{$type}.state" => 'nullable',
            "{$type}.delivery_instructions" => 'nullable',
            "{$type}.contact_email" => 'required|email',
            "{$type}.contact_phone" => 'nullable',
        ];
    }

    protected function signedSuccessUrl(int $orderId): string
    {
        return URL::temporarySignedRoute(
            'checkout-success.view',
            now()->addMinutes(60),
            ['order_id' => $orderId]
        );
    }

    public function render(): View
    {
        return view('livewire.checkout-page')
            ->layout('layouts.checkout');
    }

    public function getIsAirwallexEnabledProperty(): bool
    {
        // When the sending (redirect) gateway is active, it takes over from embedded Airwallex.
        if ($this->isSendingGatewayEnabled) {
            return false;
        }

        return filled((string) config('services.airwallex.client_id'))
            && filled((string) config('services.airwallex.api_key'));
    }

    public function getIsSendingGatewayEnabledProperty(): bool
    {
        return filled((string) config('services.sending.signing_key'));
    }

    public function getIsAirwallexRedirectModeProperty(): bool
    {
        return strtolower((string) config('lunar.airwallex.mode', 'embedded')) === 'redirect';
    }

    public function getShowPaymentGatewayLabelsProperty(): bool
    {
        return count($this->availablePaymentTypes) > 1;
    }

    public function getAvailablePaymentTypesProperty(): array
    {
        $types = [];

        if ($this->isAirwallexEnabled) {
            $types[] = 'airwallex';
        }

        if ($this->isSendingGatewayEnabled) {
            $types[] = 'sending';
        }

        return $types;
    }

    public function getSendingGatewayPacketProperty(): ?array
    {
        if (! $this->isSendingGatewayEnabled || ! $this->cart) {
            return null;
        }

        $payload = $this->airwallexLikePayload();
        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);

        if (! is_string($payloadJson)) {
            return null;
        }

        $target = (string) (config('services.sending.landing_url') ?: route('landing.special'));
        $targetWithoutQuery = strtok($target, '?') ?: $target;
        $path = trim((string) parse_url($targetWithoutQuery, PHP_URL_PATH), '/');

        parse_str((string) parse_url($target, PHP_URL_QUERY), $existingQuery);

        $query = array_merge($existingQuery, [
            'source' => parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'checkout',
            'expires' => now()->addMinutes(10)->timestamp,
            'payload_hash' => hash('sha256', $payloadJson),
        ]);

        $signature = LandingSignature::sign(
            method: 'POST',
            path: $path,
            query: $query,
            key: (string) config('services.sending.signing_key')
        );

        $signedQuery = array_merge($query, ['signature' => $signature]);

        return [
            'url' => $targetWithoutQuery.'?'.http_build_query($signedQuery, '', '&', PHP_QUERY_RFC3986),
            'payload' => $payloadJson,
        ];
    }

    protected function airwallexLikePayload(): array
    {
        $cart = $this->cart->calculate();
        $decimalPlaces = $cart->currency->decimal_places ?? 2;
        $amount = round($cart->total->value / (10 ** $decimalPlaces), $decimalPlaces);

        return [
            'amount' => $amount,
            'currency' => strtoupper($cart->currency->code),
            'merchant_order_id' => 'cart-'.$cart->id.'-'.now()->timestamp,
            'request_id' => 'req-'.$cart->id.'-'.Str::uuid()->toString(),
            'return_url' => route('checkout.view'),
        ];
    }
}
