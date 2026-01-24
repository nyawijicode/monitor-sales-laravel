<?php

namespace App\Filament\Sales\Resources\AfterSalesResource\Pages;

use App\Filament\Sales\Resources\AfterSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfterSales extends EditRecord
{
    protected static string $resource = AfterSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
