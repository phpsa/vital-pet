<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
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

Route::get('/', Home::class);

Route::middleware('guest')->group(function () {
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

Route::get('/collections/{slug}', CollectionPage::class)->name('collection.view');

Route::get('/products/{slug}', ProductPage::class)->name('product.view');

Route::get('search', SearchPage::class)->name('search.view');

Route::get('checkout', CheckoutPage::class)->name('checkout.view');

Route::get('checkout/success', CheckoutSuccessPage::class)->name('checkout-success.view');

Route::match(['GET', 'POST'], 'internal/checkout', SpecialLandingController::class)
	->middleware('external.signed')
	->name('landing.special');

Route::match(['GET', 'POST'], 'internal/mock-gateway', MockPaymentGatewayController::class)
	->name('landing.gateway.mock');
