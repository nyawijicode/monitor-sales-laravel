<?php

namespace App\Filament\Sales\Resources\RealisasiVisitResource\Pages;

use App\Filament\Sales\Resources\RealisasiVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRealisasiVisit extends ViewRecord
{
    protected static string $resource = RealisasiVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
