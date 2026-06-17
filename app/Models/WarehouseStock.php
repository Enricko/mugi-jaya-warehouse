<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseStock extends Model
{
    use Auditable, HasUuids;

    protected $table = 'warehouse_stock';

    protected string $auditModule = 'Warehouse Management';

    // This table only carries updated_at, no created_at column.
    const CREATED_AT = null;

    protected $fillable = [
        'warehouse_id',
        'material_id',
        'project_id',
        'quantity',
        'location_tag',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'updated_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Whether this stock row is at or below the material's minimum threshold.
     */
    public function isLow(): bool
    {
        return $this->material && $this->quantity <= $this->material->min_stock;
    }
}
