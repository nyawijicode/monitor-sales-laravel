<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfterSales extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'status',
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
}
