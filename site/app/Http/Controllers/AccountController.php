<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Lunar\Models\Country;
use Lunar\Models\Order;

class AccountController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('account.orders');
    }

    public function orders(): View
    {
        $user = Auth::user();

        $orders = $user
            ? Order::query()
                ->whereHas('customer.users', function ($query) use ($user) {
                    $query->whereKey($user->id);
                })
                ->whereNotNull('placed_at')
                ->latest('placed_at')
                ->latest('id')
                ->limit(20)
                ->get()
            : collect();

        return view('account.orders', [
            'activeTab' => 'orders',
            'orders' => $orders,
        ]);
    }

    public function showOrder(Request $request, Order $order): View
    {
        $user = $request->user();

        $isLinkedViaCustomer = $order->customer()
            ->whereHas('users', function ($query) use ($user) {
                $query->whereKey($user->id);
            })
            ->exists();

        $isLinkedViaUser = (int) $order->user_id === (int) $user->id;

        abort_unless($isLinkedViaCustomer || $isLinkedViaUser, 403);

        $order->load([
            'lines',
            'shippingAddress',
            'billingAddress',
            'transactions',
        ]);

        return view('account.order-show', [
            'activeTab' => 'orders',
            'order' => $order,
        ]);
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'email' => $validated['email'],
        ]);

        return back()->with('status', 'Email updated successfully.');
    }

    public function addressBook(Request $request): View
    {
        $user = Auth::user();
        $this->ensureSingleAddressDefaults($user);

        $editingAddress = null;
        $editId = (int) $request->query('edit', 0);

        if ($editId > 0) {
            $editingAddress = $user->addresses()->whereKey($editId)->first();
        }

        return view('account.address-book', [
            'activeTab' => 'address-book',
            'addresses' => $user->addresses()->with('country')->orderByDesc('is_default')->latest()->get(),
            'countries' => Country::whereIn('iso3', ['AUS', 'NZL'])->get(),
            'editingAddress' => $editingAddress,
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $this->validateAddress($request);

        $isFirstAddress = $user->addresses()->count() === 0;
        $isDefault = $isFirstAddress || $request->boolean('is_default');

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create(array_merge($validated, [
            'is_default' => $isDefault,
        ]));

        return back()->with('status', 'Address added.');
    }

    public function updateAddress(Request $request, UserAddress $address): RedirectResponse
    {
        $this->ensureAddressOwnership($request, $address);

        $validated = $this->validateAddress($request);
        $makeDefault = $request->boolean('is_default');

        if ($makeDefault) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update(array_merge($validated, [
            'is_default' => $makeDefault ? true : $address->is_default,
        ]));

        $this->ensureSingleAddressDefaults($request->user());

        return back()->with('status', 'Address updated.');
    }

    public function deleteAddress(Request $request, UserAddress $address): RedirectResponse
    {
        $this->ensureAddressOwnership($request, $address);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = $request->user()->addresses()->latest('id')->first();

            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        $this->ensureSingleAddressDefaults($request->user());

        return back()->with('status', 'Address removed.');
    }

    public function setDefaultAddress(Request $request, UserAddress $address): RedirectResponse
    {
        $this->ensureAddressOwnership($request, $address);

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('status', 'Default address updated.');
    }

    public function security(): View
    {
        return view('account.security', [
            'activeTab' => 'security',
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Password changed successfully.');
    }

    private function ensureAddressOwnership(Request $request, UserAddress $address): void
    {
        abort_unless($address->user_id === $request->user()->id, 403);
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'line_one' => ['required', 'string', 'max:255'],
            'line_two' => ['nullable', 'string', 'max:255'],
            'line_three' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:255'],
            'country_id' => ['nullable', 'exists:lunar_countries,id'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function ensureSingleAddressDefaults($user): void
    {
        $addressCount = $user->addresses()->count();

        if ($addressCount !== 1) {
            return;
        }

        $singleAddress = $user->addresses()->latest('id')->first();

        if ($singleAddress && ! $singleAddress->is_default) {
            $singleAddress->update(['is_default' => true]);
        }
    }
}
