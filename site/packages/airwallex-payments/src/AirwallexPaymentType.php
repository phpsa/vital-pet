<?php

namespace Vital\Airwallex;

use Illuminate\Support\Facades\Log;
use Throwable;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Models\Order;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\PaymentTypes\AbstractPayment;
use Vital\Airwallex\Facades\Airwallex;
use Vital\Airwallex\Models\AirwallexPaymentIntent;

class AirwallexPaymentType extends AbstractPayment
{
    public function authorize(): ?PaymentAuthorize
    {
        $intentId = (string) ($this->data['payment_intent'] ?? '');

        if (! $intentId) {
            $failure = new PaymentAuthorize(
                success: false,
                message: 'Missing Airwallex payment intent',
                paymentType: 'airwallex'
            );
            PaymentAttemptEvent::dispatch($failure);

            return $failure;
        }

        $intentModel = AirwallexPaymentIntent::firstOrCreate([
            'intent_id' => $intentId,
        ], [
            'cart_id' => $this->cart?->id,
            'order_id' => $this->order?->id,
        ]);

        if (! $intentModel->isActive()) {
            $failure = new PaymentAuthorize(
                success: false,
                message: 'Payment intent already processed',
                paymentType: 'airwallex'
            );
            PaymentAttemptEvent::dispatch($failure);

            return $failure;
        }

        $this->order = $this->order ?: ($this->cart->draftOrder ?: $this->cart->completedOrder);

        if (! $this->order) {
            try {
                $this->order = $this->cart->createOrder();
            } catch (DisallowMultipleCartOrdersException|CartException $e) {
                $failure = new PaymentAuthorize(
                    success: false,
                    message: $e->getMessage(),
                    paymentType: 'airwallex'
                );
                PaymentAttemptEvent::dispatch($failure);

                return $failure;
            }
        }

        $intentModel->update([
            'processing_at' => now(),
            'order_id' => $this->order->id,
        ]);

        $intent = Airwallex::fetchIntent($intentId) ?: [];
        $status = Airwallex::extractStatus($intent);

        $intentModel->update([
            'status' => $status,
            'processed_at' => now(),
        ]);

        $successStatuses = array_map('strtoupper', config('lunar.airwallex.success_statuses', ['SUCCEEDED']));
        $isSuccess = in_array((string) $status, $successStatuses, true);
        $transactionReference = (string) (
            data_get($intent, 'latest_payment_attempt.id')
            ?? data_get($intent, 'id')
            ?? $intentId
        );

        $this->logAuthorize('info', 'Airwallex authorize evaluated', [
            'intent_id' => $intentId,
            'cart_id' => $this->cart?->id,
            'order_id' => $this->order?->id,
            'airwallex_status' => $status,
            'transaction_reference' => $transactionReference,
            'success_statuses' => $successStatuses,
            'is_success' => $isSuccess,
            'intent_snapshot' => $this->redact($intent),
        ]);

        $this->recordOrderTransaction(
            intentId: $intentId,
            transactionReference: $transactionReference,
            status: (string) ($status ?: 'UNKNOWN'),
            isSuccess: $isSuccess,
            intent: $intent
        );

        if ($isSuccess) {
            $orderStatus = config('lunar.airwallex.status_mapping.'.$status)
                ?? config('lunar.airwallex.authorized_status', 'payment-received');

            $meta = array_merge((array) $this->order->meta, [
                'airwallex' => [
                    'intent_id' => $intentId,
                    'status' => $status,
                    'transaction_reference' => $transactionReference,
                ],
            ]);

            $this->order->update([
                'status' => $orderStatus,
                'meta' => $meta,
                'placed_at' => now(),
            ]);

            $this->logAuthorize('info', 'Airwallex payment marked successful', [
                'intent_id' => $intentId,
                'order_id' => $this->order?->id,
                'mapped_order_status' => $orderStatus,
            ]);
        } else {
            $this->logAuthorize('warning', 'Airwallex payment not successful', [
                'intent_id' => $intentId,
                'order_id' => $this->order?->id,
                'airwallex_status' => $status,
            ]);
        }

        $response = new PaymentAuthorize(
            success: $isSuccess,
            message: $isSuccess ? null : 'Payment is not completed yet',
            orderId: $this->order->id,
            paymentType: 'airwallex'
        );

        PaymentAttemptEvent::dispatch($response);

        return $response;
    }

    public function refund(TransactionContract $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        return new PaymentRefund(false, 'Refund is not implemented for Airwallex yet.');
    }

    public function capture(TransactionContract $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(false, 'Capture is not implemented for Airwallex yet.');
    }

    protected function logAuthorize(string $level, string $message, array $context = []): void
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

    protected function recordOrderTransaction(
        string $intentId,
        string $transactionReference,
        string $status,
        bool $isSuccess,
        array $intent
    ): void {
        try {
            $this->order->transactions()->create([
                'success' => $isSuccess,
                'type' => 'capture',
                'driver' => 'airwallex',
                'amount' => (int) ($this->order->total?->value ?? 0),
                'reference' => $transactionReference,
                'status' => $status,
                'notes' => $isSuccess ? 'Airwallex payment authorized' : 'Airwallex payment authorization failed',
                'card_type' => (string) (data_get($intent, 'latest_payment_attempt.payment_method.card.brand') ?: 'airwallex'),
                'last_four' => data_get($intent, 'latest_payment_attempt.payment_method.card.last4'),
                'meta' => [
                    'airwallex' => [
                        'intent_id' => $intentId,
                        'transaction_reference' => $transactionReference,
                        'status' => $status,
                        'response' => $this->redact($intent),
                    ],
                ],
            ]);
        } catch (Throwable $e) {
            $this->logAuthorize('error', 'Failed to record Airwallex order transaction', [
                'intent_id' => $intentId,
                'order_id' => $this->order?->id,
                'transaction_reference' => $transactionReference,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
