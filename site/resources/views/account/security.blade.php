<x-layouts.storefront title="Security">
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'My Orders', 'url' => route('account.orders')],
            ['label' => 'Security', 'url' => null],
        ]" />

        <section class="bg-white border border-gray-100 rounded-xl">
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100">
                <h1 class="text-lg font-medium">Security</h1>
            </div>

            @include('account._tabs', ['activeTab' => $activeTab ?? 'security'])

            <div class="p-6 space-y-8">
                @if (session('status'))
                    <div class="px-4 py-3 mb-5 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50">
                        {{ session('status') }}
                    </div>
                @endif

                <section>
                    <h2 class="text-base font-medium text-gray-900">Update Email</h2>

                    <form class="grid grid-cols-1 gap-4 mt-4 max-w-xl"
                          method="POST"
                          action="{{ route('account.security.email') }}">
                        @csrf
                        @method('PUT')

                        <label class="grid gap-1 text-sm">
                            <span class="font-medium text-gray-700">Email address</span>
                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                   name="email"
                                   type="email"
                                   value="{{ old('email', auth()->user()?->email) }}"
                                   required>
                            @error('email')
                                <span class="text-xs text-red-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <div class="flex justify-end mt-2">
                            <button class="px-5 py-3 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-900"
                                    type="submit">
                                Update Email
                            </button>
                        </div>
                    </form>
                </section>

                <section class="pt-8 border-t border-gray-100">
                    <h2 class="text-base font-medium text-gray-900">Update Password</h2>

                    <form class="grid grid-cols-1 gap-4 mt-4 max-w-xl"
                          method="POST"
                          action="{{ route('account.security.password') }}">
                        @csrf
                        @method('PUT')

                        <label class="grid gap-1 text-sm">
                            <span class="font-medium text-gray-700">Current password</span>
                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                   name="current_password"
                                   type="password"
                                   required>
                            @error('current_password')
                                <span class="text-xs text-red-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="grid gap-1 text-sm">
                            <span class="font-medium text-gray-700">New password</span>
                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                   name="password"
                                   type="password"
                                   required>
                            @error('password')
                                <span class="text-xs text-red-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="grid gap-1 text-sm">
                            <span class="font-medium text-gray-700">Confirm new password</span>
                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg"
                                   name="password_confirmation"
                                   type="password"
                                   required>
                        </label>

                        <div class="flex justify-end mt-2">
                            <button class="px-5 py-3 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-900"
                                    type="submit">
                                Update Password
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
</x-layouts.storefront>
