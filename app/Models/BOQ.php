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
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
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

    // Check if BOQ is fully approved
    public function isFullyApproved(): bool
    {
        if (!$this->persetujuan) {
            return false;
        }

        // Check if all approvers have approved
        return $this->persetujuan->approvers()
            ->where('status', '!=', 'approved')
            ->doesntExist();
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
}
