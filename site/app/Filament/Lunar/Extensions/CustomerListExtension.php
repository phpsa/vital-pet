<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Extensions;

use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Support\Extending\ResourceExtension;

final class CustomerListExtension extends ResourceExtension
{
    public function extendTable(Table $table): Table
    {
        $toggleOffByDefault = ['company_name', 'tax_identifier', 'account_ref'];

        foreach ($table->getColumns() as $column) {
            if (in_array($column->getName(), $toggleOffByDefault)) {
                $column->toggleable(isToggledHiddenByDefault: true);
            }
        }

        return $table
            ->columns([
                ...$table->getColumns(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
