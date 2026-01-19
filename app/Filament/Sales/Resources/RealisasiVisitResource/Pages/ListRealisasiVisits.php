<?php

namespace App\Filament\Sales\Resources\RealisasiVisitResource\Pages;

use App\Filament\Sales\Resources\RealisasiVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiVisits extends ListRecords
{
    protected static string $resource = RealisasiVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - realization is done from Rencana Visit
        ];
    }
}
