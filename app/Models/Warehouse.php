<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use Auditable, HasUuids;

    protected string $auditModule = 'Warehouse Management';

    protected $fillable = [
        'name',
        'address',
        'mandor_id',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The mandor responsible for this warehouse.
     */
    public function mandor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mandor_id');
    }

    /**
     * Stock rows held in this warehouse.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    /**
     * Shipments originating from this warehouse.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
