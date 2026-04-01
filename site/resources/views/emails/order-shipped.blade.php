@php
    $reference = $order->reference ?: $order->id;
    $trackingUrl          = \App\Support\TrackingLinkHelper::from($order->tracking_company, $order->shipping_tracking_number);
    $trackingCompanyLabel = \App\Support\TrackingLinkHelper::labelFrom($order->tracking_company);
@endphp

<x-email.layout title="Your order has shipped" preheader="Good news — your order #{{ $reference }} is on its way!">

    <p style="margin:0 0 20px;font-size:18px;font-weight:600;color:#111827;">
        Your order is on the way!
    </p>

    <p style="margin:0 0 24px;color:#374151;">
        Good news — your order <strong style="color:#111827;">#{{ $reference }}</strong> has been dispatched and is headed your way.
    </p>

    {{-- Shipping details box --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:28px;">
        <tr>
            <td style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:20px 24px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:#6b7280;padding-bottom:12px;">
                            Shipping Details
                        </td>
                    </tr>
                    @if($trackingCompanyLabel)
                    <tr>
                        <td style="padding-bottom:8px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="color:#6b7280;font-size:14px;width:140px;">Carrier</td>
                                    <td style="color:#111827;font-size:14px;font-weight:500;">{{ $trackingCompanyLabel }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @if($order->shipping_tracking_number)
                    <tr>
                        <td style="padding-bottom:8px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="color:#6b7280;font-size:14px;width:140px;">Tracking number</td>
                                    <td style="color:#111827;font-size:14px;font-weight:500;font-family:monospace;">{{ $order->shipping_tracking_number }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    @if($order->shipped_at)
                    <tr>
                        <td>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="color:#6b7280;font-size:14px;width:140px;">Dispatched</td>
                                    <td style="color:#111827;font-size:14px;font-weight:500;">{{ \Carbon\Carbon::parse($order->shipped_at)->format('j M Y, g:i a') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if($trackingUrl)
        <table role="presentation" cellspacing="0" cellpadding="0" style="margin-bottom:28px;">
            <tr>
                <td style="border-radius:6px;background:#111827;">
                    <a href="{{ $trackingUrl }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       style="display:inline-block;padding:12px 24px;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;">
                        Track Your Package &rarr;
                    </a>
                </td>
            </tr>
        </table>
    @endif

    <p style="margin:0;color:#6b7280;font-size:14px;">
        Thank you for shopping with us. If you have any questions, simply reply to this email.
    </p>

</x-email.layout>
