<?php

namespace App\Filament\Sales\Resources\AfterSalesResource\Pages;

use App\Filament\Sales\Resources\AfterSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAfterSales extends ListRecords
{
    protected static string $resource = AfterSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
