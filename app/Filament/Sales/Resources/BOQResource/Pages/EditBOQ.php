<?php

namespace App\Filament\Sales\Resources\BOQResource\Pages;

use App\Filament\Sales\Resources\BOQResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBOQ extends EditRecord
{
    protected static string $resource = BOQResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        // Recalculate total after items are updated
        $this->record->calculateTotal();
    }
}
