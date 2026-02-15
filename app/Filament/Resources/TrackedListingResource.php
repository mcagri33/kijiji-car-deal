<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TrackedListingResource\Pages;
use App\Models\TrackedListing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrackedListingResource extends Resource
{
    protected static ?string $model = TrackedListing::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Kijiji';

    protected static ?string $navigationLabel = 'Tracked Listings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Notification Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_tracking')
                            ->label('Tracking Active')
                            ->default(true),
                        Forms\Components\TextInput::make('last_notified_price')
                            ->label('Last Notified Price ($)')
                            ->numeric()
                            ->required(),
                        Forms\Components\Toggle::make('notify_on_price_drop')
                            ->label('Notify on Price Drop')
                            ->default(true),
                        Forms\Components\Toggle::make('notify_on_sold')
                            ->label('Notify when Sold')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kijijiListing.title')
                    ->label('Listing')
                    ->searchable()
                    ->limit(45)
                    ->tooltip(fn (TrackedListing $record): string => $record->kijijiListing->title ?? ''),
                Tables\Columns\TextColumn::make('kijijiListing.price')
                    ->label('Current Price')
                    ->money('CAD'),
                Tables\Columns\TextColumn::make('last_notified_price')
                    ->label('Last Notified Price')
                    ->money('CAD'),
                Tables\Columns\IconColumn::make('is_tracking')
                    ->label('Tracking')
                    ->boolean(),
                Tables\Columns\IconColumn::make('notify_on_price_drop')
                    ->label('Price Drop')
                    ->boolean(),
                Tables\Columns\IconColumn::make('notify_on_sold')
                    ->label('Sold')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_tracking')
                    ->label('Tracking'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_listing')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (TrackedListing $record): string => $record->kijijiListing->url)
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrackedListings::route('/'),
            'edit' => Pages\EditTrackedListing::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
