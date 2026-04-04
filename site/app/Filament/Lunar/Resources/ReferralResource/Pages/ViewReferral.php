<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Resources\ReferralResource\Pages;

use App\Filament\Lunar\Resources\ReferralResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewReferral extends ViewRecord
{
    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ban_user')
                ->label('Ban User')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => ! $this->record->isBanned())
                ->action(function (): void {
                    $this->record->update(['banned_at' => now()]);
                    $this->refreshFormData(['banned_at']);

                    Notification::make()->success()->title('User banned')->send();
                }),

            Action::make('unban_user')
                ->label('Unban User')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->isBanned())
                ->action(function (): void {
                    $this->record->update(['banned_at' => null]);
                    $this->refreshFormData(['banned_at']);

                    Notification::make()->success()->title('User unbanned')->send();
                }),

            Action::make('ban_referrer')
                ->label('Ban Referrer')
                ->icon('heroicon-o-no-symbol')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->referredBy && ! $this->record->referredBy->isBanned())
                ->action(function (): void {
                    $this->record->referredBy->update(['banned_at' => now()]);

                    Notification::make()->success()->title('Referrer banned')->send();
                }),

            Action::make('unban_referrer')
                ->label('Unban Referrer')
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->referredBy && $this->record->referredBy->isBanned())
                ->action(function (): void {
                    $this->record->referredBy->update(['banned_at' => null]);

                    Notification::make()->success()->title('Referrer unbanned')->send();
                }),

            Action::make('ban_all_referrals')
                ->label('Ban All Referrals')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('This will ban all users that this person has referred.')
                ->visible(fn () => $this->record->referrals()->whereNull('banned_at')->exists())
                ->action(function (): void {
                    $count = $this->record->referrals()->whereNull('banned_at')->count();
                    $this->record->referrals()->whereNull('banned_at')->update(['banned_at' => now()]);

                    Notification::make()->success()->title("Banned {$count} referral(s)")->send();
                }),

            Action::make('unban_all_referrals')
                ->label('Unban All Referrals')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('This will unban all users that this person has referred.')
                ->visible(fn () => $this->record->referrals()->whereNotNull('banned_at')->exists())
                ->action(function (): void {
                    $count = $this->record->referrals()->whereNotNull('banned_at')->count();
                    $this->record->referrals()->whereNotNull('banned_at')->update(['banned_at' => null]);

                    Notification::make()->success()->title("Unbanned {$count} referral(s)")->send();
                }),
        ];
    }
}
