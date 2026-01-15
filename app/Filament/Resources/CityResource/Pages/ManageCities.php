<?php

namespace App\Filament\Resources\CityResource\Pages;

use App\Filament\Resources\CityResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class ManageCities extends ManageRecords
{
    protected static string $resource = CityResource::class;

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
                        $jsonPath = database_path('seeders/data/cities.json');

                        if (!file_exists($jsonPath)) {
                            // If file doesn't exist, tell user to run the downloader
                            throw new \Exception("File not found at {$jsonPath}. Please run 'php download_data.php' in your terminal first.");
                        }

                        $cities = json_decode(file_get_contents($jsonPath), true);
                        if (!$cities) {
                            throw new \Exception("Invalid JSON data in {$jsonPath}");
                        }

                        $count = 0;
                        foreach ($cities as $city) {
                            \App\Models\City::updateOrCreate(
                                ['id' => $city['id']],
                                [
                                    'province_id' => $city['province_id'],
                                    'name' => $city['name']
                                ]
                            );
                            $count++;
                        }

                        Notification::make()
                            ->title('Sync Successful')
                            ->body("{$count} Cities/Areas synced successfully from local data.")
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
