<?php

declare(strict_types=1);

namespace App\Services;

use Lunar\Models\Cart;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Paypal\Paypal;

/**
 * Extends the Lunar PayPal integration to fix the v2 Orders API payload structure.
 *
 * The upstream package places return_url / cancel_url directly inside
 * payment_source.paypal. PayPal requires them nested inside experience_context.
 * We set them in both locations for maximum compatibility.
 */
final class PaypalService extends Paypal
{
    public function buildInitialOrder(CartContract $cart): array
    {
        /** @var Cart $cart */
        $shippingAddress = $cart->shippingAddress ?: $cart->billingAddress;

        $successRoute = config('lunar.payments.paypal.success_route', 'paypal.return');
        $cancelRoute = config('lunar.payments.paypal.cancel_route', 'checkout.view');

        $requestData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'shipping' => [
                        'type' => $shippingAddress ? 'SHIPPING' : 'PICKUP_IN_PERSON',
                        'address' => [
                            'address_line_1' => $shippingAddress?->line_one,
                            'address_line_2' => $shippingAddress?->line_two,
                            'admin_area_2' => $shippingAddress?->city,
                            'admin_area_1' => $shippingAddress?->state,
                            'postal_code' => $shippingAddress?->postcode,
                            'country_code' => $shippingAddress?->country?->iso2,
                        ],
                    ],
                    'amount' => [
                        'currency_code' => $cart->currency->code,
                        'value' => (string) $cart->total->decimal,
                    ],
                ],
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'user_action' => 'PAY_NOW',
                        'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                        'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
                        'landing_page' => 'LOGIN',
                        'return_url' => route($successRoute),
                        'cancel_url' => route($cancelRoute),
                    ],
                ],
            ],
        ];

        \Illuminate\Support\Facades\Log::debug('PayPal buildInitialOrder payload', [
            'return_url' => route($successRoute),
            'cancel_url' => route($cancelRoute),
            'payload' => $requestData,
        ]);

        $response = $this->baseHttpClient()->withToken($this->getAccessToken())->withBody(
            json_encode($requestData), 'application/json'
        )->post('v2/checkout/orders')->json();

        \Illuminate\Support\Facades\Log::debug('PayPal buildInitialOrder response', ['response' => $response]);

        return $response;
    }

    public function getOrder(string $orderId): array
    {
        $response = parent::getOrder($orderId);
        \Illuminate\Support\Facades\Log::debug('PayPal getOrder', ['orderId' => $orderId, 'response' => $response]);
        return $response;
    }

    public function capture(string $orderId): array
    {
        $response = parent::capture($orderId);
        \Illuminate\Support\Facades\Log::debug('PayPal capture', ['orderId' => $orderId, 'response' => $response]);
        return $response;
    }
}
