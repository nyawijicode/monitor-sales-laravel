<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'alt_name',
        'latitude',
        'longitude',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
