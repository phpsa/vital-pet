@php
    $reference  = $order->reference ?: '#'.$order->id;
    $buyerEmail = $order->billingAddress?->contact_email ?: $order->shippingAddress?->contact_email;
    $buyerFirst = $order->billingAddress?->first_name ?: '';
    $buyerLast  = $order->billingAddress?->last_name  ?: '';
    $buyerName  = trim($buyerFirst.' '.$buyerLast) ?: null;
@endphp

<x-email.layout title="New Order: {{ $reference }}" preheader="New paid order {{ $reference }} — {{ $order->total->formatted }}">

    {{-- Heading --}}
    <p style="margin:0 0 8px;font-size:18px;font-weight:600;color:#111827;">
        New order received
    </p>
    <p style="margin:0 0 24px;color:#374151;">
        A new paid order has been placed and requires fulfilment.
    </p>

    {{-- Order summary box --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:20px 24px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6b7280;padding-bottom:12px;">
                            Order Details
                        </td>
                    </tr>
                    @foreach ([
                        'Reference'    => $reference,
                        'Customer'     => $buyerName ?? '—',
                        'Email'        => $buyerEmail ?? '—',
                    ] as $label => $value)
                    <tr>
                        <td style="padding-bottom:6px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="color:#6b7280;font-size:14px;width:120px;">{{ $label }}</td>
                                    <td style="color:#111827;font-size:14px;font-weight:500;">{{ $value }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <td style="padding-top:10px;border-top:1px solid #e5e7eb;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="color:#374151;font-size:15px;font-weight:600;">Order Total</td>
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
        Items
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

</x-email.layout>
