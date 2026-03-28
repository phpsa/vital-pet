<?php

namespace Vital\Airwallex;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Vital\Airwallex\Components\PaymentForm;
use Vital\Airwallex\Managers\AirwallexManager;
use Vital\Airwallex\Models\AirwallexPaymentIntent;

class AirwallexPaymentsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Payments::extend('airwallex', fn ($app) => $app->make(AirwallexPaymentType::class));

        Cart::resolveRelationUsing('airwallexPaymentIntents', function (Cart $cart) {
            return $cart->hasMany(AirwallexPaymentIntent::class);
        });

        $this->app->singleton('lunar:airwallex', function ($app) {
            return $app->make(AirwallexManager::class);
        });

        Blade::directive('airwallexScripts', function () {
            return "<script src=\"".e(config('lunar.airwallex.js_url'))."\"></script>";
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'airwallex');
        $this->loadRoutesFrom(__DIR__.'/../routes/webhooks.php');

        if (! config('lunar.database.disable_migrations', false)) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        $this->mergeConfigFrom(__DIR__.'/../config/airwallex.php', 'lunar.airwallex');

        $this->publishes([
            __DIR__.'/../config/airwallex.php' => config_path('lunar/airwallex.php'),
        ], 'lunar.airwallex.config');

        if (class_exists(Livewire::class)) {
            Livewire::component('airwallex.payment', PaymentForm::class);
        }
    }
}
