<x-layouts.storefront title="Address Book">
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'My Orders', 'url' => route('orders')],
            ['label' => 'Address Book', 'url' => null],
        ]" />

        <section class="bg-white border border-gray-100 rounded-xl">
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100">
                <h1 class="text-lg font-medium">Address Book</h1>
            </div>

            @include('account._tabs', ['activeTab' => $activeTab ?? 'address-book'])

            <div class="p-6 space-y-6">
                @if (session('status'))
                    <div class="px-4 py-3 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="px-4 py-3 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50">
                        Please review the highlighted address fields and try again.
                    </div>
                @endif

                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-medium text-gray-900">Saved Addresses</h2>

                        <button class="px-4 py-2 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-900"
                                type="button"
                                onclick="openAddressModal()">
                            Add Address
                        </button>
                    </div>

                    <section class="grid gap-4">
                        @forelse ($addresses as $address)
                            <article class="p-4 border border-gray-100 rounded-lg">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h2 class="text-sm font-semibold text-gray-900">
                                            {{ $address->first_name }} {{ $address->last_name }}
                                            @if ($address->is_default)
                                                <span class="ml-2 text-xs font-medium uppercase tracking-[0.12em] text-gray-500">Default</span>
                                            @endif
                                        </h2>

                                        <div class="mt-2 text-sm text-gray-700">
                                            <p>{{ $address->line_one }}</p>
                                            @if ($address->line_two)
                                                <p>{{ $address->line_two }}</p>
                                            @endif
                                            @if ($address->line_three)
                                                <p>{{ $address->line_three }}</p>
                                            @endif
                                            <p>{{ $address->city }}, {{ $address->state }} {{ $address->postcode }}</p>
                                            <p>{{ $address->country?->native }}</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end gap-2">
                                        <div class="flex items-center gap-2">
                                            <a class="inline-flex items-center justify-center min-w-[84px] px-3 py-2 text-xs font-medium text-center text-white bg-black border border-black rounded-lg hover:bg-gray-900"
                                               href="{{ route('address-book', ['edit' => $address->id]) }}#address-modal">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('address-book.delete', $address) }}"
                                                  onsubmit="return confirm('Delete this address?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="inline-flex items-center justify-center w-9 h-9 text-red-700 bg-white border border-red-200 rounded-lg hover:bg-red-50"
                                                        type="submit">
                                                    <span class="sr-only">Delete</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                         viewBox="0 0 24 24"
                                                         fill="none"
                                                         stroke="currentColor"
                                                         class="w-4 h-4"
                                                         aria-hidden="true">
                                                        <path stroke-linecap="round"
                                                              stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-5-3h4a1 1 0 011 1v2H9V5a1 1 0 011-1z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        @if (! $address->is_default)
                                            <form method="POST"
                                                  action="{{ route('address-book.default', $address) }}">
                                                @csrf
                                                <button class="px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50"
                                                        type="submit">
                                                    Set Default
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-gray-600">No saved addresses yet.</p>
                        @endforelse
                    </section>

                    <dialog id="address-modal"
                            class="w-full max-w-3xl p-0 overflow-hidden bg-white border border-gray-100 rounded-xl shadow-2xl">
                           <section class="max-h-[90vh] overflow-y-auto">
                                    <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100">
                                        <h2 class="text-lg font-medium">
                                            {{ $editingAddress ? 'Edit Address' : 'Add Address' }}
                                        </h2>

                                        <button class="text-gray-500 hover:text-gray-700"
                                                type="button"
                                                onclick="closeAddressModal()">
                                            <span class="sr-only">Close</span>
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 class="w-5 h-5">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <form class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2"
                                          method="POST"
                                          action="{{ $editingAddress ? route('address-book.update', $editingAddress) : route('address-book.store') }}">
                                        @csrf
                                        @if ($editingAddress)
                                            @method('PUT')
                                        @endif

                                        <label class="grid gap-1 text-sm">
                                            <span class="font-medium text-gray-700">First name</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="first_name"
                                                   type="text"
                                                   value="{{ old('first_name', $editingAddress?->first_name) }}"
                                                   required>
                                        </label>

                                        <label class="grid gap-1 text-sm">
                                            <span class="font-medium text-gray-700">Last name</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="last_name"
                                                   type="text"
                                                   value="{{ old('last_name', $editingAddress?->last_name) }}"
                                                   required>
                                        </label>

                                        <label class="grid gap-1 text-sm sm:col-span-2">
                                            <span class="font-medium text-gray-700">Company name</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="company_name"
                                                   type="text"
                                                   value="{{ old('company_name', $editingAddress?->company_name) }}">
                                        </label>

                                        <label class="grid gap-1 text-sm sm:col-span-2">
                                            <span class="font-medium text-gray-700">Address line 1</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="line_one"
                                                   type="text"
                                                   value="{{ old('line_one', $editingAddress?->line_one) }}"
                                                   required>
                                        </label>

                                        <label class="grid gap-1 text-sm">
                                            <span class="font-medium text-gray-700">Address line 2</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="line_two"
                                                   type="text"
                                                   value="{{ old('line_two', $editingAddress?->line_two) }}">
                                        </label>

                                        <label class="grid gap-1 text-sm">
                                            <span class="font-medium text-gray-700">Address line 3</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="line_three"
                                                   type="text"
                                                   value="{{ old('line_three', $editingAddress?->line_three) }}">
                                        </label>

                                        <label class="grid gap-1 text-sm">
                                            <span class="font-medium text-gray-700">City</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="city"
                                                   type="text"
                                                   value="{{ old('city', $editingAddress?->city) }}"
                                                   required>
                                        </label>

                                        <label class="grid gap-1 text-sm">
                                            <span class="font-medium text-gray-700">State / Province</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="state"
                                                   type="text"
                                                   value="{{ old('state', $editingAddress?->state) }}">
                                        </label>

                                        <label class="grid gap-1 text-sm">
                                            <span class="font-medium text-gray-700">Postcode</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="postcode"
                                                   type="text"
                                                   value="{{ old('postcode', $editingAddress?->postcode) }}"
                                                   required>
                                        </label>

                                        <label class="grid gap-1 text-sm">
                                            <span class="font-medium text-gray-700">Country</span>
                                            <select class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                    name="country_id">
                                                <option value="">Select country</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}"
                                                        @selected((string) old('country_id', $editingAddress?->country_id) === (string) $country->id)>
                                                        {{ $country->native }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </label>

                                        <label class="grid gap-1 text-sm">
                                            <span class="font-medium text-gray-700">Contact phone</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="contact_phone"
                                                   type="text"
                                                   value="{{ old('contact_phone', $editingAddress?->contact_phone) }}">
                                        </label>

                                        <label class="grid gap-1 text-sm sm:col-span-2">
                                            <span class="font-medium text-gray-700">Contact email</span>
                                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                                   name="contact_email"
                                                   type="email"
                                                   value="{{ old('contact_email', $editingAddress?->contact_email) }}">
                                        </label>

                                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 sm:col-span-2">
                                            <input name="is_default"
                                                   type="checkbox"
                                                   value="1"
                                                   @checked(old('is_default', $editingAddress?->is_default))>
                                            Set as default address
                                        </label>

                                        <div class="sm:col-span-2 flex justify-end pt-2">
                                            <a class="px-5 py-3 mr-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50"
                                               href="{{ route('address-book') }}"
                                               onclick="event.preventDefault(); closeAddressModal();">
                                                Cancel
                                            </a>

                                            <button class="px-5 py-3 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-900"
                                                    type="submit">
                                                {{ $editingAddress ? 'Save Changes' : 'Add Address' }}
                                            </button>
                                        </div>
                                    </form>
                            </section>
                    </dialog>
                </div>
            </div>
        </section>
    </div>

    <style>
        #address-modal {
            margin: auto;
        }

        #address-modal::backdrop {
            background: rgba(0, 0, 0, 0.5);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('address-modal');
            const closeUrl = '{{ route('address-book') }}';

            if (!modal) {
                return;
            }

            window.openAddressModal = function () {
                if (!modal.open) {
                    modal.showModal();
                }
            };

            window.closeAddressModal = function () {
                if (modal.open) {
                    modal.close();
                }

                window.location = closeUrl;
            };

            modal.addEventListener('click', function (event) {
                const rect = modal.getBoundingClientRect();
                const inModal =
                    rect.top <= event.clientY &&
                    event.clientY <= rect.top + rect.height &&
                    rect.left <= event.clientX &&
                    event.clientX <= rect.left + rect.width;

                if (!inModal) {
                    window.closeAddressModal();
                }
            });

            @if ($editingAddress || $errors->any())
                window.openAddressModal();
            @endif
        });
    </script>
</x-layouts.storefront>
