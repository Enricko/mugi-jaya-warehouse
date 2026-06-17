<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use Auditable, HasUuids;

    protected string $auditModule = 'Shipment';

    protected $fillable = [
        'code',
        'project_id',
        'warehouse_id',
        'driver_id',
        'vehicle_plate',
        'status',
        'receiver_name',
        'receiver_signature',
        'last_gps_lat',
        'last_gps_lng',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'last_gps_lat' => 'decimal:7',
            'last_gps_lng' => 'decimal:7',
            'delivered_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }
}
