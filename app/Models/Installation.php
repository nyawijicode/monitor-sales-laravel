<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_order_id',
        'schedule_date',
        'technician_name',
        'finish_date',
        'status',
        'proof_file',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
        'finish_date' => 'datetime',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }
}
