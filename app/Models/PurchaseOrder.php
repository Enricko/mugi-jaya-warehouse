<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use Auditable, HasUuids;

    protected string $auditModule = 'Supplier';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'created_by',
        'approved_by',
        'status',
        'total_estimated',
        'needed_date',
        'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'total_estimated' => 'decimal:2',
            'needed_date' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PoItem::class);
    }
}
