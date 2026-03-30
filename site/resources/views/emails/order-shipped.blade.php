<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Shipped</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5; margin: 0; padding: 24px; background: #f9fafb;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;">
        <tr>
            <td style="padding: 24px; border-bottom: 1px solid #e5e7eb;">
                <h1 style="margin: 0; font-size: 20px;">Your order has shipped</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                @php
                    $trackingUrl = \App\Support\TrackingLinkHelper::from($order->tracking_company, $order->shipping_tracking_number);
                    $trackingCompanyLabel = \App\Support\TrackingLinkHelper::labelFrom($order->tracking_company);
                @endphp

                <p style="margin-top: 0;">Good news. Your order <strong>#{{ $order->reference ?: $order->id }}</strong> is on the way.</p>

                @if($trackingCompanyLabel)
                    <p style="margin: 8px 0 0 0;"><strong>Carrier:</strong> {{ $trackingCompanyLabel }}</p>
                @endif

                @if($order->shipping_tracking_number)
                    <p style="margin: 8px 0 0 0;"><strong>Tracking Number:</strong> {{ $order->shipping_tracking_number }}</p>
                @endif

                @if($trackingUrl)
                    <p style="margin: 14px 0 0 0;">
                        <a href="{{ $trackingUrl }}" style="display: inline-block; padding: 10px 14px; background: #111827; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;" target="_blank" rel="noopener noreferrer">
                            Track Package
                        </a>
                    </p>
                @endif

                @if($order->shipped_at)
                    <p style="margin: 8px 0 0 0;"><strong>Shipped At:</strong> {{ \Carbon\Carbon::parse($order->shipped_at)->format('j M Y, g:i a') }}</p>
                @endif

                <p style="margin: 20px 0 0 0;">Thank you for shopping with us.</p>
            </td>
        </tr>
    </table>
</body>
</html>
