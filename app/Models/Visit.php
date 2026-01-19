<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Visit extends Model
{
    protected $fillable = [
        'visit_number',
        'user_id',
        'customer_id',
        'visit_plan',
        'visit_date',
        'status_awal',
        'status_akhir',
        'activity_id',
        'photo',
        'is_join_visit',
        'keterangan',
    ];

    protected $casts = [
        'visit_plan' => 'date',
        'visit_date' => 'date',
        'is_join_visit' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($visit) {
            if (empty($visit->visit_number)) {
                $visit->visit_number = self::generateVisitNumber();
            }

            // Set default status_awal to Lead (id=1) if not set
            if (empty($visit->status_awal)) {
                $visit->status_awal = 1;
            }

            // Set user_id to current authenticated user if not set
            if (empty($visit->user_id)) {
                $visit->user_id = auth()->id();
            }
        });
    }

    protected static function generateVisitNumber(): string
    {
        // Format: RV2601-000001 (RV + YYMM + 6 digit sequential number)
        $prefix = 'RV' . date('ym') . '-';

        // Get last visit number with this prefix
        $lastVisit = self::where('visit_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastVisit) {
            // Extract number from last visit (e.g., "RV2601-000001" -> 1)
            $lastNumber = (int) str_replace($prefix, '', $lastVisit->visit_number);
            $newNumber = $lastNumber + 1;
        } else {
            // First visit for this month
            $newNumber = 1;
        }

        // Format as 6 digits: 000001, 000002, etc.
        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function statusAwal(): BelongsTo
    {
        return $this->belongsTo(CustomerStatus::class, 'status_awal');
    }

    public function statusAkhir(): BelongsTo
    {
        return $this->belongsTo(CustomerStatus::class, 'status_akhir');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class, 'activity_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'visit_participants');
    }
}
