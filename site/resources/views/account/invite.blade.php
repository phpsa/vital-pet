<x-layouts.storefront title="Invite a Friend">
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'My Orders', 'url' => route('orders')],
            ['label' => 'Invite', 'url' => null],
        ]" />

        <section class="bg-white border border-gray-100 rounded-xl">
            <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100">
                <h1 class="text-lg font-medium">Invite a Friend</h1>
            </div>

            @include('account._tabs', ['activeTab' => 'invite'])

            <div class="p-6 space-y-8">
                @if (session('status'))
                    <div class="px-4 py-3 text-sm text-green-800 border border-green-200 rounded-lg bg-green-50">
                        {{ session('status') }}
                    </div>
                @endif

                <section>
                    <h2 class="text-base font-medium text-gray-900">Send an Invitation</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Enter an email address below to send your contact a personalised invitation to register.
                    </p>

                    <form class="grid grid-cols-1 gap-4 mt-4 max-w-xl"
                          method="POST"
                          action="{{ route('account.invite.send') }}">
                        @csrf

                        <label class="grid gap-1 text-sm">
                            <span class="font-medium text-gray-700">Email address</span>
                            <input class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-black"
                                   name="email"
                                   type="email"
                                   value="{{ old('email') }}"
                                   placeholder="friend@example.com"
                                   required>
                            @error('email')
                                <span class="text-xs text-red-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <div class="flex justify-end mt-2">
                            <button class="px-5 py-3 text-sm font-medium text-white bg-black rounded-lg hover:bg-gray-900"
                                    type="submit">
                                Send Invitation
                            </button>
                        </div>
                    </form>
                </section>

                @if ($sentInvitations->isNotEmpty())
                    <section class="pt-8 border-t border-gray-100">
                        <h2 class="text-base font-medium text-gray-900">Sent Invitations</h2>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="border-b border-gray-100">
                                        <th class="py-3 pr-6 font-medium text-gray-500">Email</th>
                                        <th class="py-3 pr-6 font-medium text-gray-500">Sent</th>
                                        <th class="py-3 font-medium text-gray-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sentInvitations as $invitation)
                                        <tr class="border-b border-gray-50">
                                            <td class="py-3 pr-6 text-gray-900">{{ $invitation->email }}</td>
                                            <td class="py-3 pr-6 text-gray-500">{{ $invitation->created_at->format('d M Y') }}</td>
                                            <td class="py-3">
                                                @if ($invitation->isUsed())
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Accepted
                                                    </span>
                                                @elseif ($invitation->isExpired())
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                        Expired
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </div>
        </section>
    </div>
</x-layouts.storefront>
