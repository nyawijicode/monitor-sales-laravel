<?php

namespace App\Filament\Sales\Resources\BOQResource\Pages;

use App\Filament\Sales\Resources\BOQResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBOQS extends ListRecords
{
    protected static string $resource = BOQResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
