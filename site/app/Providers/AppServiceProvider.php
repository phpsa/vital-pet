<?php

namespace App\Providers;

use App\Filament\Lunar\Extensions\CustomerListExtension;
use App\Filament\Lunar\Extensions\OrderListExtension;
use App\Filament\Lunar\Extensions\OrderResourceExtension;
use App\Filament\Lunar\Extensions\ProductInventoryExtension;
use App\Filament\Lunar\Extensions\VariantInventoryExtension;
use App\Filament\Lunar\Pages\ContentSettingsPage;
use App\Filament\Lunar\Pages\OrderSettingsPage;
use App\Filament\Lunar\Resources\InvitationResource;
use App\Filament\Lunar\Resources\ReferralResource;
use App\Modifiers\ShippingModifier;
use App\Observers\OrderObserver;
use App\Services\PaypalService;
use App\Support\TemplateHelper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Base\ShippingModifiers;
use Lunar\Shipping\ShippingPlugin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        LunarPanel::panel(
            fn ($panel) => $panel->plugins([
                new ShippingPlugin,
            ])->resources([
                InvitationResource::class,
                ReferralResource::class,
            ])->pages([
                ContentSettingsPage::class,
                OrderSettingsPage::class,
            ])
        )
            ->register();

        LunarPanel::extensions([
            \Lunar\Admin\Filament\Resources\CustomerResource::class => CustomerListExtension::class,
            \Lunar\Admin\Filament\Resources\OrderResource\Pages\ManageOrder::class => OrderResourceExtension::class,
            \Lunar\Admin\Filament\Resources\OrderResource::class => OrderListExtension::class,
            \Lunar\Admin\Filament\Resources\ProductVariantResource\Pages\ManageVariantInventory::class => VariantInventoryExtension::class,
            \Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductInventory::class => ProductInventoryExtension::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(ShippingModifiers $shippingModifiers, Factory $viewFactory): void
    {
        // Register template view paths
        $this->registerTemplateViewPaths($viewFactory);

        // Register Blade macros for template helpers
        $this->registerBladeMacros();

        $shippingModifiers->add(
            ShippingModifier::class
        );

        \Lunar\Facades\ModelManifest::replace(
            \Lunar\Models\Contracts\Product::class,
            \App\Models\Product::class,
            // \App\Models\CustomProduct::class,
        );

        \Lunar\Models\Order::observe(OrderObserver::class);

        // Override the PayPal service with our corrected experience_context payload structure.
        // Must be in boot() so it runs after PaypalServiceProvider::boot() which also binds this interface.
        $this->app->singleton(\Lunar\Paypal\PaypalInterface::class, function ($app) {
            return $app->make(PaypalService::class);
        });
    }

    /**
     * Register view paths for the active template.
     * Views are loaded from the active template first, then from common.
     */
    private function registerTemplateViewPaths(Factory $viewFactory): void
    {
        $activeTemplate = config('template.active');

        // Prepend template paths so the default resources/views path remains
        // the final fallback when no override exists.
        if (is_dir(resource_path('views/common'))) {
            $viewFactory->prependLocation(resource_path('views/common'));
        }

        if (is_dir(resource_path("views/{$activeTemplate}"))) {
            $viewFactory->prependLocation(resource_path("views/{$activeTemplate}"));
        }
    }

    /**
     * Register Blade macros for easy template access in views
     */
    private function registerBladeMacros(): void
    {
        // Get active template name
        Blade::directive('activeTemplate', function () {
            return "<?php echo \\App\\Support\\TemplateHelper::active(); ?>";
        });

        // Get theme configuration value
        Blade::directive('theme', function ($expression) {
            return "<?php echo \\App\\Support\\TemplateHelper::themeConfig({$expression}); ?>";
        });

        // Get theme image directory
        Blade::directive('themeImageDir', function () {
            return "<?php echo \\App\\Support\\TemplateHelper::imageDir(); ?>";
        });

        // Get brand color
        Blade::directive('brandColor', function () {
            return "<?php echo \\App\\Support\\TemplateHelper::brandColor(); ?>";
        });

        // Get logo path
        Blade::directive('logo', function () {
            return "<?php echo \\App\\Support\\TemplateHelper::logo(); ?>";
        });

        // Get hero image path
        Blade::directive('heroImage', function () {
            return "<?php echo \\App\\Support\\TemplateHelper::heroImage(); ?>";
        });

        // Check if petstore
        Blade::if('petstore', function () {
            return TemplateHelper::isPetstore();
        });

        // Check if memberstore
        Blade::if('memberstore', function () {
            return TemplateHelper::ismemberstore();
        });
    }

}
