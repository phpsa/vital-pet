<div class="bg-white border border-gray-100 rounded-xl">
    <div class="flex items-center h-16 px-6 border-b border-gray-100">
        <h3 class="text-lg font-medium">
            Payment
        </h3>
    </div>

    @if ($currentStep >= $step)
        <div class="p-6 space-y-4">
            <livewire:airwallex.payment :cart="$cart"
                                        :returnUrl="route('checkout.view')" />
        </div>
    @endif
</div>
