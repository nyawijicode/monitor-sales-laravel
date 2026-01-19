<?php

namespace App\Filament\Sales\Resources\RencanaVisitResource\Pages;

use App\Filament\Sales\Resources\RencanaVisitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRencanaVisits extends ListRecords
{
    protected static string $resource = RencanaVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
