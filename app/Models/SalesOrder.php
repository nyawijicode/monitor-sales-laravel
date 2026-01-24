<?php

namespace App\Models;

use App\Interfaces\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesOrder extends Model implements HasApprovalWorkflow
{
    use HasFactory;

    public static function getApprovalType(): string
    {
        return 'Sales Order';
    }

    public static function getApprovalLabel(): string
    {
        return 'Sales Order (SO)';
    }

    protected $fillable = [
        'boq_id',
        'so_number',
        'status',
        'persetujuan_id',
        'po_spk_file',
        'npwp_file',
        'dp_invoice_file',
        'dp_payment_proof',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function boq(): BelongsTo
    {
        return $this->belongsTo(BOQ::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deliveryOrder(): HasOne
    {
        return $this->hasOne(DeliveryOrder::class);
    }

    public function afterSales(): HasOne
    {
        return $this->hasOne(AfterSales::class);
    }

    public function approvers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PersetujuanApprover::class, 'sales_order_id')->orderBy('sort_order');
    }

    // Check if SO is fully approved
    public function isFullyApproved(): bool
    {
        $totalApprovers = $this->approvers()->count();
        if ($totalApprovers === 0) return false;

        $approvedCount = $this->approvers()->where('status', 'approved')->count();
        return $totalApprovers === $approvedCount;
    }

    // Checking progress
    public function getApprovalProgress(): string
    {
        $total = $this->approvers()->count();
        $approved = $this->approvers()->where('status', 'approved')->count();
        return "{$approved}/{$total}";
    }

    public function hasAnyApprovalAction(): bool
    {
        return $this->approvers()->where('status', '!=', 'pending')->exists();
    }

    public function canBeApproved(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        if ($this->status === 'rejected') return false;

        $approver = $this->approvers()->where('user_id', $userId)->first();
        return $approver && $approver->status === 'pending';
    }

    public function approve(int $userId, ?string $notes = null): bool
    {
        if (!$this->canBeApproved($userId)) return false;

        $approver = $this->approvers()->where('user_id', $userId)->first();
        if (!$approver) return false;

        $approver->update([
            'status' => 'approved',
            'notes' => $notes,
            'action_at' => now(),
        ]);

        if ($this->isFullyApproved()) {
            $this->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $userId, // Last approver
            ]);
        }

        return true;
    }

    public function reject(int $userId, string $notes): bool
    {
        if (!$this->canBeApproved($userId)) return false;

        $approver = $this->approvers()->where('user_id', $userId)->first();
        if (!$approver) return false;

        $approver->update([
            'status' => 'rejected',
            'notes' => $notes,
            'action_at' => now(),
        ]);

        $this->update([
            'status' => 'rejected',
            'notes' => "Rejected by {$approver->user->name}: {$notes}", // Log rejection in notes
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return true;
    }

    public function resetApproval(int $userId): bool
    {
        if ($this->status === 'draft' || $this->status === 'submitted') return false;

        $this->update([
            'status' => 'submitted', // Back to submitted
            'approved_at' => null,
            'approved_by' => null,
        ]);

        $this->approvers()->update([
            'status' => 'pending',
            'notes' => null,
            'action_at' => null,
        ]);

        return true;
    }
}
