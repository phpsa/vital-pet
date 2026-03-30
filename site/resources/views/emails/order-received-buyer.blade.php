@php($reference = $order->reference ?: '#'.$order->id)
@php($buyerName = trim(($order->billingAddress?->first_name ?: '').' '.($order->billingAddress?->last_name ?: '')))

<h2>Thanks for your order</h2>

<p>
    Hi {{ $buyerName !== '' ? $buyerName : 'there' }},<br>
    We have received your order and payment successfully.
</p>

<p>
    <strong>Order:</strong> {{ $reference }}<br>
    <strong>Total:</strong> {{ $order->total->formatted }}
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
