<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyReset extends Model
{
    protected $fillable = [
        'reset_by',
        'month',
        'year',
        'revenue_snapshot',
        'orders_snapshot',
        'reset_at',
        'note',
    ];

    protected $casts = [
        'reset_at'         => 'datetime',
        'revenue_snapshot' => 'decimal:2',
    ];

    /**
     * Relasi ke user yang melakukan reset.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'reset_by');
    }

    /**
     * Ambil reset terakhir untuk bulan & tahun tertentu.
     */
    public static function latestForMonth(int $month, int $year): ?self
    {
        return static::where('month', $month)
            ->where('year', $year)
            ->latest('reset_at')
            ->first();
    }
}
