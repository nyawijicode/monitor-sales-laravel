<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BOQItem extends Model
{
    protected $table = 'boq_items';

    protected $fillable = [
        'boq_id',
        'nama_barang',
        'qty',
        'harga_barang',
        'harga_penawaran',
        'spesifikasi',
        'foto',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga_barang' => 'decimal:2',
        'harga_penawaran' => 'decimal:2',
    ];

    // Relationships
    public function boq(): BelongsTo
    {
        return $this->belongsTo(BOQ::class, 'boq_id');
    }

    // Calculate subtotal
    public function getSubtotalAttribute(): float
    {
        $price = $this->harga_penawaran ?? $this->harga_barang;
        return $this->qty * $price;
    }
}
