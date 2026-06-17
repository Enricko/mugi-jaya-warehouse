<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use Auditable, HasUuids;

    protected string $auditModule = 'Project Management';

    protected $fillable = [
        'name',
        'client_name',
        'location',
        'status',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Stock rows tagged to this project.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    /**
     * Shipments heading to this project.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
