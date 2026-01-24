<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BOQ extends Model
{
    protected $table = 'boqs';

    protected $fillable = [
        'boq_number',
        'visit_id',
        'company_id',
        'user_id',
        'persetujuan_id',
        'total_amount',
        'approval_status',
        'approval_notes',
        'approved_at',
        'approved_by',
        'previous_visit_status_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($boq) {
            if (empty($boq->boq_number)) {
                $boq->boq_number = self::generateBOQNumber();
            }

            if (empty($boq->user_id)) {
                $boq->user_id = auth()->id();
            }
        });
    }

    protected static function generateBOQNumber(): string
    {
        // Format: BOQ/000001/I/26 (BOQ + 6-digit sequential + roman month + 2-digit year)
        $month = date('m');
        $year = date('y');
        $romanMonth = self::toRoman((int)$month);

        // Get last BOQ number globally (not per month)
        $lastBOQ = self::orderBy('id', 'desc')->first();

        if ($lastBOQ) {
            // Extract number from format: BOQ/000001/I/26
            preg_match('/BOQ\/(\d+)\//', $lastBOQ->boq_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'BOQ/' . str_pad($newNumber, 6, '0', STR_PAD_LEFT) . '/' . $romanMonth . '/' . $year;
    }

    protected static function toRoman(int $number): string
    {
        $map = [
            12 => 'XII',
            11 => 'XI',
            10 => 'X',
            9 => 'IX',
            8 => 'VIII',
            7 => 'VII',
            6 => 'VI',
            5 => 'V',
            4 => 'IV',
            3 => 'III',
            2 => 'II',
            1 => 'I'
        ];

        return $map[$number] ?? 'I';
    }

    // Calculate total amount from items
    public function calculateTotal(): void
    {
        $total = $this->items->sum(function ($item) {
            $price = $item->harga_penawaran ?? $item->harga_barang;
            return $item->qty * $price;
        });

        $this->update(['total_amount' => $total]);
    }

    // Get BOQ-specific approvers (not template approvers)
    public function approvers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PersetujuanApprover::class, 'boq_id')->orderBy('sort_order');
    }

    // Check if BOQ is fully approved (ALL approvers must approve)
    public function isFullyApproved(): bool
    {
        $totalApprovers = $this->approvers()->count();
        if ($totalApprovers === 0) {
            return false;
        }

        // Check if ALL approvers have approved
        $approvedCount = $this->approvers()
            ->where('status', 'approved')
            ->count();

        return $totalApprovers === $approvedCount;
    }

    // Get approval progress (e.g., "2/3 approved")
    public function getApprovalProgress(): string
    {
        $totalApprovers = $this->approvers()->count();
        $approvedCount = $this->approvers()
            ->where('status', 'approved')
            ->count();

        return "{$approvedCount}/{$totalApprovers}";
    }

    // Check if current user can approve this BOQ
    public function canBeApproved(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        // BOQ must not be finalized (rejected)
        if ($this->approval_status === 'rejected') {
            return false;
        }

        // Check if user is one of the approvers AND hasn't taken action yet
        $approver = $this->approvers()
            ->where('user_id', $userId)
            ->first();

        return $approver && $approver->status === 'pending';
    }

    // Approve the BOQ by specific approver
    public function approve(int $userId, ?string $notes = null): bool
    {
        if (!$this->canBeApproved($userId)) {
            return false;
        }

        // Update specific approver status
        $approver = $this->approvers()
            ->where('user_id', $userId)
            ->first();

        if (!$approver) {
            return false;
        }

        $approver->update([
            'status' => 'approved',
            'notes' => $notes,
            'action_at' => now(),
        ]);

        // Log the approval
        $this->approvalHistory()->create([
            'user_id' => $userId,
            'action' => 'approved',
            'notes' => $notes,
        ]);

        // Check if ALL approvers have approved
        if ($this->isFullyApproved()) {
            $this->update([
                'approval_status' => 'approved',
                'approval_notes' => 'Disetujui oleh semua approver',
                'approved_at' => now(),
                'approved_by' => $userId, // Last approver
            ]);

            // Update Visit Status to Deal/PO/SPK
            if ($this->visit) {
                // Save previous status if not already saved
                if (is_null($this->previous_visit_status_id)) {
                    $this->update(['previous_visit_status_id' => $this->visit->status_akhir]);
                }

                // Find Deal/PO/SPK status
                // Try to find status that resembles Deal/PO/SPK
                $dealStatus = \App\Models\CustomerStatus::where(function ($query) {
                    $query->where('name', 'like', '%Deal%')
                        ->orWhere('name', 'like', '%PO%')
                        ->orWhere('name', 'like', '%SPK%');
                })->first();

                if ($dealStatus) {
                    $this->visit->update(['status_akhir' => $dealStatus->id]);
                }
            }
        }

        return true;
    }

    // Reject the BOQ by specific approver
    public function reject(int $userId, string $notes): bool
    {
        if (!$this->canBeApproved($userId)) {
            return false;
        }

        // Update specific approver status
        $approver = $this->approvers()
            ->where('user_id', $userId)
            ->first();

        if (!$approver) {
            return false;
        }

        $approver->update([
            'status' => 'rejected',
            'notes' => $notes,
            'action_at' => now(),
        ]);

        // Immediately mark BOQ as rejected (any rejection = rejected)
        $this->update([
            'approval_status' => 'rejected',
            'approval_notes' => $notes,
            'approved_at' => now(),
            'approved_by' => $userId,
        ]);

        // Update Visit Status to Lost
        if ($this->visit) {
            // Save previous status if not already saved
            if (is_null($this->previous_visit_status_id)) {
                $this->update(['previous_visit_status_id' => $this->visit->status_akhir]);
            }

            // Find Lost status
            $lostStatus = \App\Models\CustomerStatus::where(function ($query) {
                $query->where('name', 'like', '%Lost%')
                    ->orWhere('name', 'like', '%Gagal%')
                    ->orWhere('name', 'like', '%Cancel%')
                    ->orWhere('name', 'like', '%Tolak%');
            })->first();

            if ($lostStatus) {
                $this->visit->update(['status_akhir' => $lostStatus->id]);
            }
        }

        // Log the rejection
        $this->approvalHistory()->create([
            'user_id' => $userId,
            'action' => 'rejected',
            'notes' => $notes,
        ]);

        return true;
    }

    // Reset approval (Super Admin only)
    public function resetApproval(int $userId): bool
    {
        if ($this->approval_status === 'pending') {
            return false;
        }

        // Reset BOQ status
        $this->update([
            'approval_status' => 'pending',
            'approval_notes' => null,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        // Revert Visit Status if it was changed
        if ($this->visit && !is_null($this->previous_visit_status_id)) {
            $this->visit->update(['status_akhir' => $this->previous_visit_status_id]);
            $this->update(['previous_visit_status_id' => null]);
        }

        // Reset ALL approvers back to pending
        $this->approvers()->update([
            'status' => 'pending',
            'notes' => null,
            'action_at' => null,
        ]);

        // Log the reset
        $this->approvalHistory()->create([
            'user_id' => $userId,
            'action' => 'reset',
            'notes' => 'Approval reset by Super Admin',
        ]);

        return true;
    }

    // Relationships
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function persetujuan(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Persetujuan::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BOQItem::class, 'boq_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvalHistory(): HasMany
    {
        return $this->hasMany(BOQApproval::class, 'boq_id')->orderBy('created_at', 'desc');
    }
}
