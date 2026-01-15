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
        'user_id',
        'sort_order',
    ];

    public function persetujuan(): BelongsTo
    {
        return $this->belongsTo(Persetujuan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
