<x-layouts.storefront title="My Orders">
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'My Orders', 'url' => route('orders')],
            ['label' => 'Orders', 'url' => null],
        ]" />

        <section class="bg-white border border-gray-100 rounded-xl">
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100">
                <h1 class="text-lg font-medium">Orders</h1>
            </div>

            @include('account._tabs', ['activeTab' => $activeTab ?? 'orders'])

            <div class="p-6">
                @if ($orders->isEmpty())
                    <p class="text-sm text-gray-600">You have no orders yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px] table-fixed text-sm">
                            <colgroup>
                                <col class="w-[18%]">
                                <col class="w-[22%]">
                                <col class="w-[30%]">
                                <col class="w-[14%]">
                                <col class="w-[16%]">
                            </colgroup>
                            <thead>
                                <tr class="text-left text-gray-500 border-b border-gray-100">
                                    <th class="py-3 px-4 font-medium">Reference</th>
                                    <th class="py-3 px-4 font-medium">Status</th>
                                    <th class="py-3 px-4 font-medium">Placed</th>
                                    <th class="py-3 px-4 font-medium text-right">Total</th>
                                    <th class="py-3 px-4 font-medium text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr class="border-b border-gray-100 last:border-b-0">
                                        <td class="py-4 px-4 font-medium text-gray-900 whitespace-nowrap">{{ $order->reference ?: ('#' . $order->id) }}</td>
                                        <td class="py-4 px-4 text-gray-700 whitespace-nowrap">{{ ucfirst((string) $order->status) }}</td>
                                        <td class="py-4 px-4 text-gray-700 whitespace-nowrap">{{ optional($order->placed_at)->format('j M Y, g:i a') ?? '-' }}</td>
                                        <td class="py-4 px-4 text-right text-gray-900 whitespace-nowrap">{{ $order->total->formatted }}</td>
                                        <td class="py-4 px-4 text-right whitespace-nowrap">
                                            <a class="inline-flex items-center justify-center px-4 py-2 text-xs font-medium text-white bg-black border border-black rounded-lg hover:bg-gray-900"
                                               href="{{ route('orders.show', $order) }}">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-layouts.storefront>
