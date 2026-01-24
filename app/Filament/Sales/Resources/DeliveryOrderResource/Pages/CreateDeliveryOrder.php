<?php

namespace App\Filament\Sales\Resources\DeliveryOrderResource\Pages;

use App\Filament\Sales\Resources\DeliveryOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliveryOrder extends CreateRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate DO Number if empty
        if (empty($data['do_number'])) {
            $year = date('y');
            $month = date('m');
            // Format: DO/YYMM/XXXX (e.g. DO/2601/0001)
            $prefix = "DO/{$year}{$month}/";

            // Find last DO number
            $lastDo = \App\Models\DeliveryOrder::where('do_number', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastDo) {
                // Extract number
                $lastNumber = intval(substr($lastDo->do_number, -4));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $data['do_number'] = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        }

        return $data;
    }
}
