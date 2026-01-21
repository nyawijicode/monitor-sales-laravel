<?php

namespace App\Filament\Sales\Resources\BOQResource\Pages;

use App\Filament\Sales\Resources\BOQResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBOQ extends CreateRecord
{
    protected static string $resource = BOQResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function afterCreate(): void
    {
        // Calculate total after items are created
        $this->record->calculateTotal();
    }
}
