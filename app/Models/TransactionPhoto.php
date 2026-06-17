<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionPhoto extends Model
{
    use HasUuids;

    protected $fillable = [
        'transaction_id',
        'photo_path',
        'captured_at',
        'gps_lat',
        'gps_lng',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'gps_lat' => 'decimal:7',
            'gps_lng' => 'decimal:7',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
