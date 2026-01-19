<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'kode_customer',
        'nama_instansi',
        'alamat',
        'nama_kontak',
        'telepon',
        'jabatan',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->kode_customer)) {
                // Get the last customer code
                $lastCustomer = static::orderBy('id', 'desc')->first();

                if ($lastCustomer && $lastCustomer->kode_customer) {
                    // Extract number from last code (e.g., "C.000001" -> 1)
                    $lastNumber = (int) str_replace('C.', '', $lastCustomer->kode_customer);
                    $newNumber = $lastNumber + 1;
                } else {
                    // First customer
                    $newNumber = 1;
                }

                // Format as C.000001
                $customer->kode_customer = 'C.' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
