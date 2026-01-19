<?php

namespace App\Filament\Sales\Resources\RealisasiVisitResource\Pages;

use App\Filament\Sales\Resources\RealisasiVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRealisasiVisit extends EditRecord
{
    protected static string $resource = RealisasiVisitResource::class;

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
}
