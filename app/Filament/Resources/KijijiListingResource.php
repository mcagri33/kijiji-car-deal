<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\KijijiListingResource\Pages;
use App\Models\KijijiListing;
use App\Models\TrackedListing;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KijijiListingResource extends Resource
{
    protected static ?string $model = KijijiListing::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Kijiji';

    protected static ?string $navigationLabel = 'Listings';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (KijijiListing $record): string => $record->title),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('CAD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'sold' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_checked_at')
                    ->label('Last Checked')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('tracked')
                    ->label('Tracked')
                    ->boolean()
                    ->getStateUsing(fn (KijijiListing $record): bool => $record->isTracked()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'sold' => 'Sold',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('track')
                    ->label('Track')
                    ->icon('heroicon-o-heart')
                    ->color('success')
                    ->visible(fn (KijijiListing $record): bool => ! $record->isTracked() && $record->status === 'active')
                    ->action(function (KijijiListing $record): void {
                        TrackedListing::create([
                            'kijiji_listing_id' => $record->id,
                            'last_notified_price' => $record->price,
                            'notify_on_price_drop' => true,
                            'notify_on_sold' => true,
                        ]);
                    })
                    ->successNotificationTitle('Listing is now being tracked.'),
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (KijijiListing $record): string => $record->url)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKijijiListings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
