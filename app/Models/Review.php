<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id', 'customer_name', 'order_id', 'rating', 'comment', 'is_featured',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
