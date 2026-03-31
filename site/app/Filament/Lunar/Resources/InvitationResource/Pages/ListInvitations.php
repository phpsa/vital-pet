<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Resources\InvitationResource\Pages;

use App\Filament\Lunar\Resources\InvitationResource;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Mail;

class ListInvitations extends ListRecords
{
    protected static string $resource = InvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_staff_invitation')
                ->label('Send Staff Invitation')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    TextInput::make('email')
                        ->label('Recipient Email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->rules([
                            fn () => function (string $attribute, mixed $value, \Closure $fail) {
                                if (User::where('email', $value)->exists()) {
                                    $fail('This email address already has an account.');
                                }

                                $pending = Invitation::where('email', $value)
                                    ->whereNull('used_at')
                                    ->where('expires_at', '>', now())
                                    ->exists();

                                if ($pending) {
                                    $fail('A pending invitation has already been sent to this address.');
                                }
                            },
                        ]),
                ])
                ->action(function (array $data): void {
                    $invitation = Invitation::generate(
                        email: $data['email'],
                        invitedByUserId: null,
                        isStaffInvite: true,
                    );

                    Mail::to($data['email'])->send(new InvitationMail($invitation));

                    Notification::make()
                        ->success()
                        ->title('Invitation sent to '.$data['email'])
                        ->send();
                }),
        ];
    }
}
