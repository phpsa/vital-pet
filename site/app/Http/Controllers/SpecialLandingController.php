<?php

namespace App\Http\Controllers;

use App\Models\LandingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Throwable;
use Illuminate\View\View;
use Vital\Airwallex\Facades\Airwallex;

class SpecialLandingController
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->isMethod('get') && ! $request->query('signature')) {
            return $this->handleGatewayReturn($request);
        }

        $payloadRaw = $request->input('payload');
        $decodedPayload = null;

        if (is_string($payloadRaw) && $payloadRaw !== '') {
            $decoded = json_decode($payloadRaw, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $decodedPayload = $decoded;
            }
        }

        abort_unless(is_array($decodedPayload), 422, 'Invalid payload');

        $requestId = (string) ($decodedPayload['request_id'] ?? '');
        $returnUrl = (string) ($decodedPayload['return_url'] ?? '');

        abort_unless($requestId !== '' && $returnUrl !== '', 422, 'Missing request_id or return_url');

        $gatewayReturnUrl = route('landing.special', ['request_id' => $requestId]);
        $gatewayPayload = $decodedPayload;
        $gatewayPayload['return_url'] = $gatewayReturnUrl;

        $landingRequest = LandingRequest::query()->updateOrCreate(
            ['request_id' => $requestId],
            [
                'merchant_order_id' => $decodedPayload['merchant_order_id'] ?? null,
                'return_url' => $returnUrl,
                'gateway_return_url' => $gatewayReturnUrl,
                'status' => 'intent_creating',
                'payload' => $decodedPayload,
                'gateway_payload' => $gatewayPayload,
                'meta' => [
                    'source' => $request->query('source'),
                    'expires' => $request->query('expires'),
                ],
            ]
        );

        try {
            $intent = Airwallex::createIntentFromPayload($gatewayPayload);

            $landingRequest->update([
                'status' => 'gateway_redirect_pending',
                'gateway_posted_at' => now(),
                'meta' => array_merge((array) $landingRequest->meta, [
                    'airwallex_intent_id' => data_get($intent, 'id'),
                    'airwallex_status' => data_get($intent, 'status'),
                    'airwallex_intent' => Arr::except($intent, ['client_secret']),
                ]),
            ]);
        } catch (Throwable $exception) {
            $landingRequest->update([
                'status' => 'intent_create_failed',
                'meta' => array_merge((array) $landingRequest->meta, [
                    'airwallex_error' => $exception->getMessage(),
                ]),
            ]);

            return view('landing.special', [
                'mode' => 'error',
                'landingRequest' => $landingRequest->fresh(),
                'errorMessage' => $exception->getMessage(),
            ]);
        }

        $landingRequest = $landingRequest->fresh();
        $intentMeta = (array) $landingRequest->meta;

        return view('landing.special', [
            'mode' => 'handoff',
            'landingRequest' => $landingRequest,
            'airwallexIntentId' => $intentMeta['airwallex_intent_id'] ?? null,
            'airwallexClientSecret' => Airwallex::getClientSecret($intent),
            'airwallexCurrency' => $gatewayPayload['currency'] ?? null,
            'airwallexCountryCode' => null,
            'airwallexJsUrl' => config('lunar.airwallex.js_url'),
            'airwallexEnv' => config('lunar.airwallex.env', 'demo'),
        ]);
    }

    protected function handleGatewayReturn(Request $request): View|RedirectResponse
    {
        $requestId = (string) $request->query('request_id', '');
        $landingRequest = LandingRequest::query()->firstWhere('request_id', $requestId);

        abort_unless($landingRequest, 404);

        $landingRequest->update([
            'status' => (string) ($request->query('gateway_status') ?: 'returned'),
            'returned_at' => now(),
            'meta' => array_merge((array) $landingRequest->meta, [
                'gateway_return_query' => $request->query(),
            ]),
        ]);

        if ((string) $request->query('type') === 'CANCEL_URL') {
            $landingRequest->update(['status' => 'cancelled']);

            return redirect()->away(
                $this->appendQueryParameter($landingRequest->return_url, 'type', 'CANCEL_URL')
            );
        }

        if ((string) $request->query('type') === 'SUCCESS_URL') {
            $landingRequest->update(['status' => 'success']);

            $returnUrl = $landingRequest->return_url;

            // Forward the Airwallex intent id so the sender can verify server-side
            if ($request->query('id')) {
                $returnUrl = $this->appendQueryParameter($returnUrl, 'id', (string) $request->query('id'));
            }

            $returnUrl = $this->appendQueryParameter($returnUrl, 'type', 'SUCCESS_URL');

            return redirect()->away($returnUrl);
        }

        return view('landing.special', [
            'mode' => 'return',
            'landingRequest' => $landingRequest->fresh(),
        ]);
    }

    protected function appendQueryParameter(string $url, string $key, string $value): string
    {
        $fragment = parse_url($url, PHP_URL_FRAGMENT);
        $baseUrl = strtok($url, '#') ?: $url;

        parse_str((string) parse_url($baseUrl, PHP_URL_QUERY), $query);
        $query[$key] = $value;

        $path = strtok($baseUrl, '?') ?: $baseUrl;
        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $finalUrl = $queryString !== '' ? $path.'?'.$queryString : $path;

        return $fragment ? $finalUrl.'#'.$fragment : $finalUrl;
    }
}