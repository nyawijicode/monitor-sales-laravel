<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'do_number',
        'status',
        'invoice_file',
        'do_file_unsigned',
        'do_file_signed',
        'checklist_file',
        'shipping_type',
        'schedule_date',
        'courier_name',
        'receipt_number',
        'photos',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'photos' => 'array',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function installation(): HasOne
    {
        return $this->hasOne(Installation::class);
    }
}
