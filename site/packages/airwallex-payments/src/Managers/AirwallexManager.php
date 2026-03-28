<?php

namespace Vital\Airwallex\Managers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Cart;
use Lunar\Models\Contracts\Cart as CartContract;

class AirwallexManager
{
    public function fetchOrCreateIntent(CartContract $cart, array $createOptions = []): array
    {
        /** @var Cart $cart */
        $existingIntentId = $this->getCartIntentId($cart);

        if ($existingIntentId) {
            $existingIntent = $this->fetchIntent($existingIntentId);

            if ($existingIntent && $this->getClientSecret($existingIntent)) {
                return $existingIntent;
            }
        }

        return $this->createIntent($cart, $createOptions);
    }

    public function getCartIntentId(CartContract $cart): ?string
    {
        /** @var Cart $cart */
        return $cart->airwallexPaymentIntents()->active()->first()?->intent_id;
    }

    public function createIntent(CartContract $cart, array $opts = []): array
    {
        /** @var Cart $cart */
        $cart = $cart->calculate();

        $decimalPlaces = $cart->currency->decimal_places ?? 2;
        $amount = round($cart->total->value / (10 ** $decimalPlaces), $decimalPlaces);

        $payload = [
            'amount' => $amount,
            'currency' => strtoupper($cart->currency->code),
            'merchant_order_id' => 'cart-'.$cart->id.'-'.now()->timestamp,
            'request_id' => 'req-'.$cart->id.'-'.str()->uuid()->toString(),
            'return_url' => $opts['return_url'] ?? null,
        ];

        $payload = array_filter($payload, fn ($value) => ! is_null($value));

        $response = $this->request('post', config('lunar.airwallex.intent.create_endpoint'), $payload);

        $intentId = (string) data_get($response, 'id', '');

        $cart->airwallexPaymentIntents()->updateOrCreate(
            ['intent_id' => $intentId],
            [
                'status' => $this->extractStatus($response),
            ]
        );

        return $response;
    }

    public function fetchIntent(string $intentId): ?array
    {
        if (! $intentId) {
            return null;
        }

        $endpoint = str_replace('{intent_id}', $intentId, config('lunar.airwallex.intent.retrieve_endpoint'));

        return $this->request('get', $endpoint);
    }

    public function getCheckoutUrl(array $intent): ?string
    {
        return data_get($intent, 'next_action.url')
            ?? data_get($intent, 'next_action.redirect.url')
            ?? data_get($intent, 'redirect_url')
            ?? data_get($intent, 'checkout.url');
    }

    public function getClientSecret(array $intent): ?string
    {
        return data_get($intent, 'client_secret')
            ?? data_get($intent, 'payment_consent.client_secret')
            ?? data_get($intent, 'payment_method_options.card.client_secret');
    }

    public function extractStatus(array $intent): ?string
    {
        return data_get($intent, 'status')
            ? strtoupper((string) data_get($intent, 'status'))
            : null;
    }

    protected function request(string $method, string $endpoint, array $payload = []): array
    {
        try {
            $response = $this->client()->{$method}($endpoint, $payload)->throw();
            $body = $response->json() ?: [];

            $this->logApi('info', 'Airwallex API response', [
                'method' => strtoupper($method),
                'endpoint' => $endpoint,
                'status_code' => $response->status(),
                'request_payload' => $this->redact($payload),
                'response_body' => $this->redact($body),
            ]);

            return $body;
        } catch (RequestException $e) {
            $response = $e->response;

            $this->logApi('error', 'Airwallex API request failed', [
                'method' => strtoupper($method),
                'endpoint' => $endpoint,
                'status_code' => $response?->status(),
                'request_payload' => $this->redact($payload),
                'response_body' => $this->redact($response?->json() ?: []),
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('lunar.airwallex.api_base'), '/'))
            ->acceptJson()
            ->asJson()
            ->withToken($this->getAccessToken())
            ->withHeaders([
                'x-client-id' => (string) config('services.airwallex.client_id'),
                'x-api-key' => (string) config('services.airwallex.api_key'),
            ]);
    }

    protected function getAccessToken(): string
    {
        return Cache::remember('airwallex_access_token', now()->addMinutes(20), function () {
            $response = Http::baseUrl(rtrim((string) config('lunar.airwallex.api_base'), '/'))
                ->acceptJson()
                ->asJson()
                ->withHeaders([
                    'x-client-id' => (string) config('services.airwallex.client_id'),
                    'x-api-key' => (string) config('services.airwallex.api_key'),
                ])
                ->post('/api/v1/authentication/login', [
                    'grant_type' => 'client_credentials',
                ])
                ->throw()
                ->json();

            return (string) data_get($response, 'token', '');
        });
    }

    protected function logApi(string $level, string $message, array $context = []): void
    {
        if (! config('lunar.airwallex.log_api_responses', true)) {
            return;
        }

        Log::channel(config('logging.default'))->{$level}($message, $context);
    }

    protected function redact(array $data): array
    {
        $redacted = [];

        foreach ($data as $key => $value) {
            $isSensitive = is_string($key) && preg_match('/token|secret|api[_-]?key|authorization/i', $key);

            if ($isSensitive) {
                $redacted[$key] = '[REDACTED]';

                continue;
            }

            if (is_array($value)) {
                $redacted[$key] = $this->redact($value);

                continue;
            }

            $redacted[$key] = $value;
        }

        return $redacted;
    }
}
