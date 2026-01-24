<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersetujuanApprover extends Model
{
    use HasFactory;

    protected $fillable = [
        'persetujuan_id',
        'boq_id',
        'sales_order_id',
        'after_sales_id',
        'user_id',
        'sort_order',
        'status',
        'notes',
        'action_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function persetujuan(): BelongsTo
    {
        return $this->belongsTo(Persetujuan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function boq(): BelongsTo
    {
        return $this->belongsTo(BOQ::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function afterSales(): BelongsTo
    {
        return $this->belongsTo(AfterSales::class);
    }
}
