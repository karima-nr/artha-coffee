<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method',
        'amount_paid',
        'change',
        'proof_of_payment',
        'status',
        'transaction_id',
        'payment_type',
        'gross_amount',
        'transaction_status',
        'transaction_time',
        'fraud_status',
        'snap_token',
        'qr_url',
        'expiry_time',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
