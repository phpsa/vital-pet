<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Extensions;

use App\Models\ProductVariantRegionalStock;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Support\Extending\EditPageExtension;
use Lunar\Models\Country;
use Lunar\Models\ProductVariant;

final class VariantInventoryExtension extends EditPageExtension
{
    public function extendForm(Form $form): Form
    {
        $currentSchema = $form->getComponents();

        return $form->schema([
            ...$currentSchema,
            Forms\Components\Section::make('regional_stock')
                ->heading('Regional Stock')
                ->description('Override stock levels per country. When enabled, country-specific rows apply for shoppers whose shipping address matches a configured country.')
                ->schema([
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
                ]),
        ]);
    }

    public function beforeFill(array $data): array
    {
        $variant = $this->caller->getRecord();

        $data['regional_stocks'] = ProductVariantRegionalStock::where('product_variant_id', $variant->id)
            ->get()
            ->map(fn ($r) => [
                'country_id' => $r->country_id,
                'stock'      => $r->stock,
                'backorder'  => $r->backorder,
            ])
            ->toArray();

        return $data;
    }

    public function beforeSave(array $data): array
    {
        if (! array_key_exists('regional_stocks', $data)) {
            return $data;
        }

        /** @var ProductVariant $variant */
        $variant = $this->caller->getRecord();

        $this->syncRegionalStocks($variant, $data['regional_stocks'] ?? []);

        unset($data['regional_stocks']);

        return $data;
    }

    private function syncRegionalStocks(ProductVariant $variant, array $rows): void
    {
        $incoming = collect($rows)
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
    }
}
