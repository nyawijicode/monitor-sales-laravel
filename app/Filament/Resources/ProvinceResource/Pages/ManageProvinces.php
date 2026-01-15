<?php

namespace App\Filament\Resources\ProvinceResource\Pages;

use App\Filament\Resources\ProvinceResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ManageProvinces extends ManageRecords
{
    protected static string $resource = ProvinceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('sync')
                ->label('Sync from Google')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    try {
                        // Use local data file
                        $jsonPath = database_path('seeders/data/provinces.json');

                        if (!file_exists($jsonPath)) {
                            throw new \Exception('Local data file not found: ' . $jsonPath);
                        }

                        $provinces = json_decode(file_get_contents($jsonPath), true);

                        foreach ($provinces as $province) {
                            \App\Models\Province::updateOrCreate(
                                ['id' => $province['id']],
                                ['name' => $province['name']]
                            );
                        }

                        Notification::make()
                            ->title('Sync Successful')
                            ->body('Provinces synced successfully from local data.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sync Failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
