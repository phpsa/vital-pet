<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Lunar\Facades\CartSession;
use Lunar\Facades\Payments;

final class PaypalReturnController extends Controller
{
    public function __invoke()
    {
        $paypalOrderId = (string) request('token', '');

        if ($paypalOrderId === '') {
            Log::error('PayPal return: missing token parameter');

            return redirect()->route('checkout.view')
                ->with('error', 'Payment failed: missing order information.');
        }

        $cart = CartSession::current();

        if (! $cart) {
            Log::error('PayPal return: cart not found', ['token' => $paypalOrderId]);

            return redirect()->route('checkout.view')
                ->with('error', 'Payment failed: cart not found.');
        }

        try {
            $payment = Payments::driver('paypal')
                ->cart($cart)
                ->withData([
                    'paypal_order_id' => $paypalOrderId,
                    'status' => 'payment-received',
                ])
                ->authorize();
        } catch (\Throwable $e) {
            Log::error('PayPal authorize exception', [
                'token' => $paypalOrderId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('checkout.view')
                ->with('error', 'Payment error: ' . $e->getMessage());
        }

        Log::debug('PayPal authorize result', [
            'token' => $paypalOrderId,
            'success' => $payment->success,
            'orderId' => $payment->orderId ?? null,
            'message' => $payment->message ?? null,
        ]);

        if ($payment->success && $payment->orderId) {
            CartSession::forget();

            return redirect(
                URL::temporarySignedRoute(
                    'checkout-success.view',
                    now()->addMinutes(60),
                    ['order_id' => $payment->orderId]
                )
            );
        }

        Log::error('PayPal payment authorization failed', [
            'token' => $paypalOrderId,
            'success' => $payment->success,
            'orderId' => $payment->orderId ?? null,
            'message' => $payment->message ?? null,
        ]);

        return redirect()->route('checkout.view')
            ->with('error', 'Payment failed: ' . ($payment->message ?: 'please try again.'));
    }
}
