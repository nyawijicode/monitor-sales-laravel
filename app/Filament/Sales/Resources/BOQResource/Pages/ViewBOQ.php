<?php

namespace App\Filament\Sales\Resources\BOQResource\Pages;

use App\Filament\Sales\Resources\BOQResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBOQ extends ViewRecord
{
    protected static string $resource = BOQResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn($record) => !$record->hasAnyApprovalAction()),
        ];
    }
}
