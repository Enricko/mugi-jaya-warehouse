<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use Auditable, HasUuids;

    protected string $auditModule = 'Supplier';

    protected $fillable = [
        'name',
        'address',
        'contact_phone',
        'city',
        'is_external_island',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_external_island' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Purchase orders issued to this supplier.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
