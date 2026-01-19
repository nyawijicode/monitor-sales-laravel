<?php

namespace App\Filament\Sales\Resources\RencanaVisitResource\Pages;

use App\Filament\Sales\Resources\RencanaVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRencanaVisit extends EditRecord
{
    protected static string $resource = RencanaVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
