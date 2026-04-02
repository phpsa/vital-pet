<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\Order;

class CheckoutSuccessPage extends Component
{
    public ?Cart $cart;

    public ?int $order_id = null;

    public Order $order;

    protected $queryString = [
        'order_id',
    ];

    public function mount(): void
    {
        if ($this->order_id) {
            if (! request()->hasValidSignature()) {
                abort(403);
            }

            $order = Order::query()->find($this->order_id);

            if ($order) {
                $this->order = $order;
                CartSession::forget();

                return;
            }

            abort(404);
        }

        $this->cart = CartSession::current();
        if (! $this->cart || ! $this->cart->completedOrder) {
            $this->redirect('/');

            return;
        }
        $this->order = $this->cart->completedOrder;

        CartSession::forget();
    }

    public function render(): View
    {
        return view('livewire.checkout-success-page')
            ->title('Order Confirmed');
    }
}
