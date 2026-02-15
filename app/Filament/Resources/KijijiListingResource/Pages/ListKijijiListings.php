<?php

declare(strict_types=1);

namespace App\Filament\Resources\KijijiListingResource\Pages;

use App\Filament\Resources\KijijiListingResource;
use Filament\Resources\Pages\ListRecords;

class ListKijijiListings extends ListRecords
{
    protected static string $resource = KijijiListingResource::class;
}
