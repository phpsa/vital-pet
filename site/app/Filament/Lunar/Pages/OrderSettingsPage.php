<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Pages;

use App\Settings\OrderSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Lunar\Models\Country;

class OrderSettingsPage extends SettingsPage
{
    protected static string $settings = OrderSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Order Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 96;

    protected static ?string $title = 'Order Settings';

    protected static string $view = 'filament-spatie-laravel-settings-plugin::pages.settings-page';

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Storefront Countries')
                ->description('Select the countries available for the storefront country switcher. The first selected country is used as the default for new sessions.')
                ->schema([
                    Select::make('storefront_country_iso2')
                        ->label('Enabled Countries')
                        ->multiple()
                        ->searchable()
                        ->options(fn () => Country::orderBy('name')->pluck('name', 'iso2')->toArray())
                        ->helperText('Countries available in the storefront country selector.'),
                ]),
            Section::make('Minimum Order Value')
                ->description('Set a minimum subtotal customers must reach before they can proceed to checkout. Set to 0 to disable.')
                ->schema([
                    TextInput::make('min_order_value')
                        ->label('Minimum Order Value')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->placeholder('0.00')
                        ->helperText('Enter the minimum cart subtotal required to proceed to checkout. Use 0 to disable this check.')
                        ->prefix('$'),
                ]),
        ]);
    }
}
