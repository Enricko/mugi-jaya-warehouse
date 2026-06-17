<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use Auditable, HasUuids;

    protected string $auditModule = 'Warehouse Management';

    protected $fillable = [
        'sku',
        'name',
        'unit',
        'purchase_price',
        'min_stock',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'min_stock' => 'integer',
        ];
    }

    /**
     * Stock rows for this material across warehouses.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    /**
     * Total quantity of this material across all warehouses.
     */
    public function totalQuantity(): float
    {
        return (float) $this->stocks()->sum('quantity');
    }
}
