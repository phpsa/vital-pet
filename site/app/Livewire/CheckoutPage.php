<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
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
     * The payment type we want to use.
     */
    public string $paymentType = 'airwallex';

    /**
     * {@inheritDoc}
     */
    protected $listeners = [
        'cartUpdated' => 'refreshCart',
        'selectedShippingOption' => 'refreshCart',
    ];

    public $payment_intent = null;

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
        $this->paymentType = 'airwallex';

        if (! $this->cart = CartSession::current()) {
            $this->redirect('/');

            return;
        }

        if ($this->payment_intent) {
            $payment = Payments::driver($this->paymentType)->cart($this->cart)->withData([
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
        $this->paymentType = 'airwallex';
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
}
