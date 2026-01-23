<?php

namespace App\Filament\Sales\Resources\BOQResource\Pages;

use App\Filament\Sales\Resources\BOQResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBOQ extends CreateRecord
{
    protected static string $resource = BOQResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function afterCreate(): void
    {
        // Calculate total after items are created
        $this->record->calculateTotal();

        // Auto-select persetujuan based on user and company
        $companyId = $this->record->company_id;
        $userId = auth()->id();

        // Find or create persetujuan for this user + company combination
        $persetujuan = \App\Models\Persetujuan::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->first();

        // If persetujuan exists, link it to BOQ
        if ($persetujuan) {
            $this->record->update(['persetujuan_id' => $persetujuan->id]);
        }
    }
}
