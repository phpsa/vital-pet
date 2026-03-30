<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Extensions;

use App\Enums\ShippingCarrier;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Lunar\Admin\Support\Extending\ViewPageExtension;

final class OrderResourceExtension extends ViewPageExtension
{
    public function extendInfolistAsideSchema(array $components): array
    {
        return [
            ...$components,
            $this->shippingTrackingSection(),
        ];
    }

    protected function shippingTrackingSection(): Infolists\Components\Section
    {
        return Infolists\Components\Section::make('shipping_tracking')
            ->heading('Shipping Tracking')
            ->compact()
            ->schema([
                Infolists\Components\TextEntry::make('shipping_tracking_number')
                    ->label('Tracking Number')
                    ->placeholder('No tracking number')
                    ->copyable()
                    ->copyMessage('Tracking number copied')
                    ->copyMessageDuration(1200),
                Infolists\Components\TextEntry::make('tracking_company')
                    ->label('Tracking Company')
                    ->placeholder('Not specified')
                    ->formatStateUsing(fn ($state) => ShippingCarrier::tryFromLoose($state)?->label() ?: $state),
                Infolists\Components\TextEntry::make('shipped_at')
                    ->label('Shipped At')
                    ->dateTime()
                    ->placeholder('Not shipped'),
            ])
            ->headerActions([
                Infolists\Components\Actions\Action::make('edit_tracking')
                    ->label('Edit Tracking')
                    ->icon('heroicon-o-pencil')
                    ->color('gray')
                    ->button()
                    ->size('xs')
                    ->form([
                        Forms\Components\TextInput::make('shipping_tracking_number')
                            ->label('Tracking Number')
                            ->maxLength(255),
                        Forms\Components\Select::make('tracking_company')
                            ->label('Tracking Company')
                            ->options(ShippingCarrier::options())
                            ->searchable(),
                    ])
                    ->fillForm(fn ($record): array => [
                        'shipping_tracking_number' => $record->shipping_tracking_number,
                        'tracking_company' => ShippingCarrier::tryFromLoose($record->tracking_company)?->value,
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'shipping_tracking_number' => $data['shipping_tracking_number'] ?: null,
                            'tracking_company' => $data['tracking_company'] ?: null,
                            'shipped_at' => ! empty($data['shipping_tracking_number']) && ! $record->shipped_at
                                ? now()
                                : $record->shipped_at,
                        ]);

                        Notification::make()
                            ->title('Tracking details saved')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
