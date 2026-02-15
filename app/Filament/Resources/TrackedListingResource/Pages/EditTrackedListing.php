<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackedListingResource\Pages;

use App\Filament\Resources\TrackedListingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrackedListing extends EditRecord
{
    protected static string $resource = TrackedListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
