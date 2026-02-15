<?php

declare(strict_types=1);

namespace App\Filament\Resources\KijijiFilterResource\Pages;

use App\Filament\Resources\KijijiFilterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKijijiFilter extends EditRecord
{
    protected static string $resource = KijijiFilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
