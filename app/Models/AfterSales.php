<?php

namespace App\Models;

use App\Interfaces\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfterSales extends Model implements HasApprovalWorkflow
{
    use HasFactory;

    public static function getApprovalType(): string
    {
        return 'After Sales';
    }

    public static function getApprovalLabel(): string
    {
        return 'After Sales (Garansi & Pelunasan)';
    }

    protected $fillable = [
        'sales_order_id',
        'status',
        'persetujuan_id',
        'final_billing_file',
        'payment_proof_file',
        'warranty_letter_file',
        'warranty_status',
        'approved_by',
        'notes',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PersetujuanApprover::class, 'after_sales_id')->orderBy('sort_order');
    }

    public function isFullyApproved(): bool
    {
        $totalApprovers = $this->approvers()->count();
        if ($totalApprovers === 0) return false;

        $approvedCount = $this->approvers()->where('status', 'approved')->count();
        return $totalApprovers === $approvedCount;
    }

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

        if ($this->warranty_status === 'rejected') return false;

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
                'warranty_status' => 'approved',
                'approved_by' => $userId,
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
            'warranty_status' => 'rejected',
            'notes' => "Rejected by {$approver->user->name}: {$notes}",
            'approved_by' => null,
        ]);

        return true;
    }

    public function resetApproval(int $userId): bool
    {
        if ($this->warranty_status === 'draft') return false;

        $this->update([
            'warranty_status' => 'draft',
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
