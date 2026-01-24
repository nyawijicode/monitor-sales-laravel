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

    protected function afterCreate(): void
    {
        // Auto-select persetujuan based on user and company (Context is usually implicit or from BOQ)
        // Here we assume context from authenticated user or linked BOQ's company

        $boq = $this->record->boq;
        $companyId = $boq->company_id ?? null;
        $userId = auth()->id();

        if (!$companyId) return;

        // Find persetujuan template for this user + company + Type 'Sales Order'
        $persetujuan = \App\Models\Persetujuan::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->where('name', 'Sales Order')
            ->first();

        // If persetujuan exists, link it and clone approvers
        if ($persetujuan) {
            $this->record->update([
                'persetujuan_id' => $persetujuan->id,
                'status' => 'submitted', // Auto submit if approval workflow exists
            ]);

            // Clone approvers from template to this SO
            $templateApprovers = $persetujuan->approvers;

            foreach ($templateApprovers as $approver) {
                \App\Models\PersetujuanApprover::create([
                    'persetujuan_id' => $persetujuan->id,
                    'sales_order_id' => $this->record->id, // Linked to SO
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
