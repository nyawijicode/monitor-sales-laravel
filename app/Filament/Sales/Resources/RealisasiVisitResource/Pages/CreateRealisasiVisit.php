<?php

namespace App\Filament\Sales\Resources\RealisasiVisitResource\Pages;

use App\Filament\Sales\Resources\RealisasiVisitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRealisasiVisit extends CreateRecord
{
    protected static string $resource = RealisasiVisitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
