<section class="bg-white"
         style="padding-bottom: 6rem;">
    <div class="max-w-screen-xl px-4 pt-8 pb-20 mx-auto sm:px-6 sm:pt-10 lg:px-8 lg:pt-12 lg:pb-24">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Checkout', 'url' => route('checkout.view')],
            ['label' => 'Order Complete', 'url' => null],
        ]" />

        <div class="max-w-xl mx-auto pb-12 text-center sm:pb-16 lg:pb-20">
            <h1 class="mt-8 text-3xl font-extrabold sm:text-5xl">
                <span class="block"
                      role="img">
                    🥳
                </span>

                <span class="block mt-1 text-blue-500">
                    Order Successful!
                </span>
            </h1>

            <p class="mt-4 font-medium sm:text-lg">
                Your order reference number is

                <strong>
                    {{ $order->reference }}
                </strong>
            </p>

            <a class="inline-block px-8 py-3 mt-8 text-sm font-medium text-center text-white bg-black rounded-lg hover:ring-1 hover:ring-black"
               href="{{ url('/') }}">
                Back Home
            </a>
        </div>
    </div>
</section>
