<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Resources;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReferralResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'referrals';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Referrals';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 91;

    protected static ?string $modelLabel = 'Referral';

    protected static ?string $pluralModelLabel = 'Referrals';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('User')
                ->schema([
                    Infolists\Components\TextEntry::make('name')->label('Name'),
                    Infolists\Components\TextEntry::make('email')->label('Email'),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Registered')
                        ->dateTime('d M Y H:i'),
                ]),

            Infolists\Components\Section::make('Referred By')
                ->schema([
                    Infolists\Components\TextEntry::make('referredBy.name')
                        ->label('Name')
                        ->placeholder('No referrer (direct or staff invite)'),
                    Infolists\Components\TextEntry::make('referredBy.email')
                        ->label('Email')
                        ->placeholder('—'),
                ]),

            Infolists\Components\Section::make('People They Have Referred')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('referrals')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('name')->label('Name'),
                            Infolists\Components\TextEntry::make('email')->label('Email'),
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Joined')
                                ->dateTime('d M Y'),
                        ])
                        ->columns(3)
                        ->placeholder('No referrals yet'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('referredBy.name')
                    ->label('Referred By')
                    ->placeholder('—')
                    ->sortable(false),

                Tables\Columns\TextColumn::make('referrals_count')
                    ->label('Referred Others')
                    ->counts('referrals')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_referrer')
                    ->label('Has a referrer')
                    ->query(fn ($query) => $query->whereNotNull('referred_by_id')),

                Tables\Filters\Filter::make('has_referrals')
                    ->label('Has referred others')
                    ->query(fn ($query) => $query->has('referrals')),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Lunar\Resources\ReferralResource\Pages\ListReferrals::route('/'),
            'view'  => \App\Filament\Lunar\Resources\ReferralResource\Pages\ViewReferral::route('/{record}'),
        ];
    }
}
