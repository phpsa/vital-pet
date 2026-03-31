<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Extensions;

use App\Models\ProductVariantRegionalStock;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Lunar\Admin\Support\Extending\EditPageExtension;
use Lunar\Models\Country;

final class ProductInventoryExtension extends EditPageExtension
{
    public function headerActions(array $actions): array
    {
        return [
            ...$actions,
            Action::make('manage_regional_stock')
                ->label('Regional Stock')
                ->icon('heroicon-o-globe-alt')
                ->color('gray')
                ->modalHeading('Regional Stock')
                ->modalDescription('Override stock levels per country. When enabled, country rows are used for shoppers whose shipping address matches. Regional stock is consulted first; global stock acts as a cross-country fallback.')
                ->modalWidth('4xl')
                ->form([
                    Forms\Components\Toggle::make('regional_stock_enabled')
                        ->label('Enable regional stock')
                        ->helperText('When disabled, global stock/backorder values apply to all countries.')
                        ->live(),

                    Forms\Components\Repeater::make('regional_stocks')
                        ->label('Country stock allocations')
                        ->visible(fn (Forms\Get $get) => (bool) $get('regional_stock_enabled'))
                        ->schema([
                            Forms\Components\Select::make('country_id')
                                ->label('Country')
                                ->options(fn () => Country::orderBy('name')->pluck('name', 'id')->toArray())
                                ->searchable()
                                ->required()
                                ->distinct(),

                            Forms\Components\TextInput::make('stock')
                                ->label('Stock')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),

                            Forms\Components\TextInput::make('backorder')
                                ->label('Backorder')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->required(),
                        ])
                        ->columns(3)
                        ->reorderable(false)
                        ->addActionLabel('Add Country'),
                ])
                ->fillForm(function ($record): array {
                    $variant = $record->variants()->withTrashed()->first();

                    if (! $variant) {
                        return ['regional_stock_enabled' => false, 'regional_stocks' => []];
                    }

                    return [
                        'regional_stock_enabled' => (bool) $variant->regional_stock_enabled,
                        'regional_stocks' => ProductVariantRegionalStock::where('product_variant_id', $variant->id)
                            ->get()
                            ->map(fn ($r) => [
                                'country_id' => $r->country_id,
                                'stock'      => $r->stock,
                                'backorder'  => $r->backorder,
                            ])
                            ->toArray(),
                    ];
                })
                ->action(function (array $data, $record): void {
                    $variant = $record->variants()->withTrashed()->first();

                    if (! $variant) {
                        return;
                    }

                    $variant->update([
                        'regional_stock_enabled' => (bool) ($data['regional_stock_enabled'] ?? false),
                    ]);

                    $incoming = collect($data['regional_stocks'] ?? [])
                        ->filter(fn ($r) => ! empty($r['country_id']))
                        ->keyBy('country_id');

                    ProductVariantRegionalStock::where('product_variant_id', $variant->id)
                        ->whereNotIn('country_id', $incoming->keys()->toArray() ?: [0])
                        ->delete();

                    foreach ($incoming as $countryId => $row) {
                        ProductVariantRegionalStock::updateOrCreate(
                            [
                                'product_variant_id' => $variant->id,
                                'country_id'         => (int) $countryId,
                            ],
                            [
                                'stock'     => max(0, (int) ($row['stock'] ?? 0)),
                                'backorder' => max(0, (int) ($row['backorder'] ?? 0)),
                            ]
                        );
                    }

                    Notification::make()
                        ->title('Regional stock saved')
                        ->success()
                        ->send();
                }),
        ];
    }
}
