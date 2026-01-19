<?php

namespace App\Filament\Sales\Resources\RencanaVisitResource\Pages;

use App\Filament\Sales\Resources\RencanaVisitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRencanaVisit extends CreateRecord
{
    protected static string $resource = RencanaVisitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
