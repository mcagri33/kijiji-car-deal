<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackedListingResource\Pages;

use App\Filament\Resources\TrackedListingResource;
use Filament\Resources\Pages\ListRecords;

class ListTrackedListings extends ListRecords
{
    protected static string $resource = TrackedListingResource::class;
}
