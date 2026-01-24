<?php

namespace App\Filament\Sales\Resources\InstallationResource\Pages;

use App\Filament\Sales\Resources\InstallationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstallation extends EditRecord
{
    protected static string $resource = InstallationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
