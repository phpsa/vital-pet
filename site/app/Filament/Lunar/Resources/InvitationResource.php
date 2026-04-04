<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Resources;

use App\Models\Invitation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Lunar\Models\CustomerGroup;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Invitations';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 90;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')
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
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invitedByUser.name')
                    ->label('Invited By')
                    ->default('Staff')
                    ->sortable(false),

                Tables\Columns\TextColumn::make('customerGroup.name')
                    ->label('Target Group')
                    ->default('—')
                    ->badge()
                    ->sortable(false),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn (Invitation $record) => match (true) {
                        $record->isUsed()    => 'accepted',
                        $record->isExpired() => 'expired',
                        default              => 'pending',
                    })
                    ->colors([
                        'success' => 'accepted',
                        'gray'    => 'expired',
                        'warning' => 'pending',
                    ])
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('used_at')
                    ->label('Accepted At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('pending')
                    ->label('Pending')
                    ->query(fn (Builder $query) => $query->whereNull('used_at')->where('expires_at', '>', now())),

                Tables\Filters\Filter::make('accepted')
                    ->label('Accepted')
                    ->query(fn (Builder $query) => $query->whereNotNull('used_at')),

                Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(fn (Builder $query) => $query->whereNull('used_at')->where('expires_at', '<=', now())),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Lunar\Resources\InvitationResource\Pages\ListInvitations::route('/'),
        ];
    }
}
