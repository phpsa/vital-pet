<nav class="border-b border-gray-100" aria-label="Account tabs">
    <div class="flex flex-wrap gap-2 px-4 py-3 sm:px-6">
        <a class="px-3 py-2 text-sm font-medium rounded-lg {{ ($activeTab ?? '') === 'orders' ? 'bg-black text-white' : 'text-gray-700 hover:bg-gray-100' }}"
           href="{{ route('account.orders') }}">
            Orders
        </a>

        <a class="px-3 py-2 text-sm font-medium rounded-lg {{ ($activeTab ?? '') === 'address-book' ? 'bg-black text-white' : 'text-gray-700 hover:bg-gray-100' }}"
           href="{{ route('account.address-book') }}">
            Address Book
        </a>

        <a class="px-3 py-2 text-sm font-medium rounded-lg {{ ($activeTab ?? '') === 'security' ? 'bg-black text-white' : 'text-gray-700 hover:bg-gray-100' }}"
           href="{{ route('account.security') }}">
            Security
        </a>
    </div>
</nav>
