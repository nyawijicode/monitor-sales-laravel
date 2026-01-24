<?php

namespace App\Filament\Sales\Resources\SalesOrderResource\Pages;

use App\Filament\Sales\Resources\SalesOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesOrder extends CreateRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate SO Number if empty
        if (empty($data['so_number'])) {
            $year = date('y');
            $month = date('m');
            // Format: SO/YYMM/XXXX (e.g. SO/2601/0001)
            $prefix = "SO/{$year}{$month}/";

            // Find last SO number
            $lastSo = \App\Models\SalesOrder::where('so_number', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastSo) {
                // Extract number
                $lastNumber = intval(substr($lastSo->so_number, -4));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $data['so_number'] = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        }

        return $data;
    }
}
