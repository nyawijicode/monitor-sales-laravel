<?php

namespace App\Filament\Sales\Resources\AfterSalesResource\Pages;

use App\Filament\Sales\Resources\AfterSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAfterSales extends CreateRecord
{
    protected static string $resource = AfterSalesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Auto-select persetujuan based on user and company (Context from SO->BOQ->Company)
        $salesOrder = $this->record->salesOrder;
        $boq = $salesOrder->boq ?? null;
        $companyId = $boq->company_id ?? null;
        $userId = auth()->id();

        if (!$companyId) return;

        // Find persetujuan template for this user + company + Type 'After Sales'
        // Note: Assuming 'After Sales' is the name we agreed on
        $persetujuan = \App\Models\Persetujuan::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->where('name', 'After Sales')
            ->first();

        // If persetujuan exists, link it and clone approvers
        if ($persetujuan) {
            $this->record->update([
                'persetujuan_id' => $persetujuan->id,
                // warranty_status usually starts as 'draft', user might need to submit it manually or auto-submit
                // For consistency with SO, let's keep it draft until approved? Or auto-submit?
                // The prompt for SO was "Submitted -> Approved". 
                // Let's assume auto-attach allows approval process to start.
            ]);

            // Clone approvers from template to this AfterSales
            $templateApprovers = $persetujuan->approvers;

            foreach ($templateApprovers as $approver) {
                \App\Models\PersetujuanApprover::create([
                    'persetujuan_id' => $persetujuan->id,
                    'after_sales_id' => $this->record->id,
                    'user_id' => $approver->user_id,
                    'sort_order' => $approver->sort_order,
                    'status' => 'pending',
                    'notes' => null,
                    'action_at' => null,
                ]);
            }
        }
    }
}
