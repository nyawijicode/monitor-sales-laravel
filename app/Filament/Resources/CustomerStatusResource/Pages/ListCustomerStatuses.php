<?php

namespace App\Filament\Resources\CustomerStatusResource\Pages;

use App\Filament\Resources\CustomerStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerStatuses extends ListRecords
{
    protected static string $resource = CustomerStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
