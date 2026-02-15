<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\KijijiFilterResource\Pages;
use App\Models\KijijiFilter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KijijiFilterResource extends Resource
{
    protected static ?string $model = KijijiFilter::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationGroup = 'Kijiji';

    protected static ?string $navigationLabel = 'Search Filters';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Vehicle Criteria')
                    ->schema([
                        Forms\Components\TextInput::make('make')
                            ->label('Make')
                            ->placeholder('e.g. Honda, Toyota')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('model')
                            ->label('Model')
                            ->placeholder('e.g. Civic, Camry')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->label('Location')
                            ->default('ontario')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Price & Year')
                    ->schema([
                        Forms\Components\TextInput::make('min_price')
                            ->label('Min Price ($)')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('max_price')
                            ->label('Max Price ($)')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\TextInput::make('min_year')
                            ->label('Min Year')
                            ->numeric()
                            ->minValue(1990)
                            ->maxValue(2030),
                        Forms\Components\TextInput::make('max_year')
                            ->label('Max Year')
                            ->numeric()
                            ->minValue(1990)
                            ->maxValue(2030),
                        Forms\Components\TextInput::make('max_km')
                            ->label('Max KM')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('e.g. 150000'),
                    ])
                    ->columns(5),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Only active filters are scanned by the scheduler.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('make')
                    ->label('Make')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_price')
                    ->label('Min $')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_price')
                    ->label('Max $')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_year')
                    ->label('Min Year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_year')
                    ->label('Max Year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
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
            'index' => Pages\ListKijijiFilters::route('/'),
            'create' => Pages\CreateKijijiFilter::route('/create'),
            'edit' => Pages\EditKijijiFilter::route('/{record}/edit'),
        ];
    }
}
