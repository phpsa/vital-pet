@php($reference = $order->reference ?: '#'.$order->id)
@php($buyerEmail = $order->billingAddress?->contact_email ?: $order->shippingAddress?->contact_email)

<h2>New Order Received</h2>

<p>A new paid order has been created.</p>

<p>
    <strong>Order:</strong> {{ $reference }}<br>
    <strong>Total:</strong> {{ $order->total->formatted }}<br>
    <strong>Buyer Email:</strong> {{ $buyerEmail ?: 'N/A' }}
</p>

<h3>Items</h3>
<ul>
    @foreach ($order->lines as $line)
        <li>
            {{ $line->description }} x {{ $line->quantity }}
            ({{ $line->total->formatted }})
        </li>
    @endforeach
</ul>
