<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AccountController;
use App\Livewire\CheckoutPage;
use App\Livewire\CheckoutSuccessPage;
use App\Livewire\CollectionPage;
use App\Livewire\Home;
use App\Livewire\ProductPage;
use App\Livewire\SearchPage;
use App\Http\Controllers\MockPaymentGatewayController;
use App\Http\Controllers\SpecialLandingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')->group(function () {
	Route::middleware('registration.open')->group(function () {
		Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
		Route::post('register', [RegisteredUserController::class, 'store']);
	});

	Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
	Route::post('login', [AuthenticatedSessionController::class, 'store']);

	Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
		->name('password.request');
	Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
		->name('password.email');

	Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
		->name('password.reset');
	Route::post('reset-password', [NewPasswordController::class, 'store'])
		->name('password.update');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
	->middleware('auth')
	->name('logout');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
	Route::get('/', [AccountController::class, 'index'])->name('index');
	Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
	Route::get('/orders/{order}', [AccountController::class, 'showOrder'])->name('orders.show');
	Route::get('/address-book', [AccountController::class, 'addressBook'])->name('address-book');
	Route::post('/address-book', [AccountController::class, 'storeAddress'])->name('address-book.store');
	Route::put('/address-book/{address}', [AccountController::class, 'updateAddress'])->name('address-book.update');
	Route::delete('/address-book/{address}', [AccountController::class, 'deleteAddress'])->name('address-book.delete');
	Route::post('/address-book/{address}/default', [AccountController::class, 'setDefaultAddress'])->name('address-book.default');
	Route::get('/security', [AccountController::class, 'security'])->name('security');
	Route::put('/security/email', [AccountController::class, 'updateEmail'])->name('security.email');
	Route::put('/security/password', [AccountController::class, 'updatePassword'])->name('security.password');
});

Route::middleware('storefront.auth_lock')->group(function () {
	Route::get('/', Home::class);

	Route::get('/collections/{slug}', CollectionPage::class)->name('collection.view');

	Route::get('/products/{slug}', ProductPage::class)->name('product.view');

	Route::get('search', SearchPage::class)->name('search.view');

	Route::get('checkout', CheckoutPage::class)->name('checkout.view');

	Route::get('checkout/success', CheckoutSuccessPage::class)->name('checkout-success.view');
});

Route::match(['GET', 'POST'], 'internal/checkout', SpecialLandingController::class)
	->middleware('external.signed')
	->name('landing.special');

Route::match(['GET', 'POST'], 'internal/mock-gateway', MockPaymentGatewayController::class)
	->name('landing.gateway.mock');
