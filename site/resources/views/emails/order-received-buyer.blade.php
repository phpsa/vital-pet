@php
    $reference  = $order->reference ?: '#'.$order->id;
    $buyerFirst = $order->billingAddress?->first_name ?: '';
    $buyerLast  = $order->billingAddress?->last_name  ?: '';
    $buyerName  = trim($buyerFirst.' '.$buyerLast) ?: null;
@endphp

<x-email.layout title="Order Confirmation {{ $reference }}" preheader="Your order {{ $reference }} has been received. Thank you!">

    {{-- Greeting --}}
    <p style="margin:0 0 20px;font-size:18px;font-weight:600;color:#111827;">
        Thanks for your order{{ $buyerName ? ', '.explode(' ', $buyerName)[0] : '' }}!
    </p>

    <p style="margin:0 0 24px;color:#374151;">
        We've received your order and your payment was successful.
        We'll let you know when it ships.
    </p>

    {{-- Order summary box --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:20px 24px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6b7280;padding-bottom:12px;">
                            Order Summary
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom:8px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="color:#374151;font-size:14px;">Order reference</td>
                                    <td align="right" style="color:#111827;font-size:14px;font-weight:600;">{{ $reference }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:10px;border-top:1px solid #e5e7eb;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="color:#374151;font-size:15px;font-weight:600;">Total</td>
                                    <td align="right" style="color:#111827;font-size:15px;font-weight:700;">{{ $order->total->formatted }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Items --}}
    <p style="margin:0 0 12px;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6b7280;">
        Items Ordered
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:28px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
        @foreach ($order->lines as $line)
            <tr>
                <td style="padding:14px 18px;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}font-size:14px;color:#374151;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <span style="font-weight:500;color:#111827;">{{ $line->description }}</span>
                                @if ((bool) data_get($line->meta, 'inventory.is_backorder', false))
                                    <span style="display:inline-block;margin-left:8px;padding:2px 8px;background:#fef3c7;color:#92400e;font-size:11px;font-weight:600;border-radius:4px;">Backorder</span>
                                @endif
                                <br>
                                <span style="color:#6b7280;font-size:13px;">Qty: {{ $line->quantity }}</span>
                            </td>
                            <td align="right" style="font-weight:600;color:#111827;white-space:nowrap;vertical-align:top;">
                                {{ $line->total->formatted }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endforeach
    </table>

    {{-- Footer message --}}
    <p style="margin:0;color:#6b7280;font-size:14px;">
        Thank you for shopping with us. If you have any questions about your order, simply reply to this email.
    </p>

</x-email.layout>
