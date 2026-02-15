<?php

declare(strict_types=1);

namespace App\Filament\Resources\KijijiFilterResource\Pages;

use App\Filament\Resources\KijijiFilterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKijijiFilters extends ListRecords
{
    protected static string $resource = KijijiFilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
