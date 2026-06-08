<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasUuids;

    const UPDATED_AT = null; // Audit logs are immutable, no updated_at column

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'entity_type',
        'entity_id',
        'before_data',
        'after_data',
        'ip_address',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the entity that was modified.
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
