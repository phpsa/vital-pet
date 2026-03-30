<?php

namespace Vital\Airwallex\Components;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Lunar\Models\Contracts\Cart as CartContract;
use Vital\Airwallex\Facades\Airwallex;

class PaymentForm extends Component
{
    public CartContract $cart;

    public ?string $returnUrl = null;

    public ?string $buttonLabel = null;

    public string $mode;

    public function mount(): void
    {
        $this->mode = (string) config('lunar.airwallex.mode', 'embedded');
    }

    #[Computed]
    public function intent(): array
    {
        $baseUrl = $this->returnUrl ?: url()->current();

        return Airwallex::fetchOrCreateIntent($this->cart, [
            'return_url' => $baseUrl,
        ]);
    }

    #[Computed]
    public function clientSecret(): ?string
    {
        return Airwallex::getClientSecret($this->intent);
    }

    #[Computed]
    public function intentId(): ?string
    {
        return data_get($this->intent, 'id');
    }

    #[Computed]
    public function checkoutUrl(): ?string
    {
        return Airwallex::getCheckoutUrl($this->intent);
    }

    #[Computed]
    public function callbackUrl(): string
    {
        $baseUrl = $this->returnUrl ?: url()->current();

        $query = array_filter([
            'payment_intent' => data_get($this->intent, 'id'),
        ]);

        return $query ? $baseUrl.'?'.http_build_query($query) : $baseUrl;
    }

    public function render()
    {
        return view('airwallex::airwallex.components.payment-form');
    }
}
