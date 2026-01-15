<?php

namespace App\Filament\Resources\CustomerStatusResource\Pages;

use App\Filament\Resources\CustomerStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerStatus extends EditRecord
{
    protected static string $resource = CustomerStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
