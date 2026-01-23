<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BOQApproval extends Model
{
    use HasFactory;

    protected $table = 'boq_approvals'; // Fix: Laravel pluralizes BOQApproval incorrectly

    protected $fillable = [
        'boq_id',
        'user_id',
        'action',
        'notes',
    ];

    /**
     * Get the BOQ that this approval belongs to
     */
    public function boq(): BelongsTo
    {
        return $this->belongsTo(BOQ::class);
    }

    /**
     * Get the user who performed this approval action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
