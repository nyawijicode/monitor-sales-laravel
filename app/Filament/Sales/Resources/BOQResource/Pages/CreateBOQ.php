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

        // Find persetujuan template for this user + company
        $persetujuan = \App\Models\Persetujuan::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->where('name', 'BOQ')
            ->first();

        // If persetujuan exists, link it and clone approvers
        if ($persetujuan) {
            $this->record->update(['persetujuan_id' => $persetujuan->id]);

            // Clone approvers from template to this BOQ
            // Each BOQ gets its own set of approvers with fresh 'pending' status
            $templateApprovers = $persetujuan->approvers; // Will get only template approvers (whereNull boq_id)

            foreach ($templateApprovers as $approver) {
                \App\Models\PersetujuanApprover::create([
                    'persetujuan_id' => $persetujuan->id,
                    'boq_id' => $this->record->id,
                    'user_id' => $approver->user_id,
                    'sort_order' => $approver->sort_order,
                    'status' => 'pending', // Always start as pending
                    'notes' => null,
                    'action_at' => null,
                ]);
            }
        }
    }
}
