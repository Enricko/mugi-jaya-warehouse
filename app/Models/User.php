<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Traits\Auditable;

class User extends Authenticatable
{
    use Auditable, HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected string $auditModule = 'Authentication & Security';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'email',
        'password_hash',
        'full_name',
        'phone',
        'role',
        'ktp_photo_encrypted',
        'created_by',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password_hash',
        'ktp_photo_encrypted',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Override the default password column for authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ──────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────

    /**
     * The user (superior) who registered this user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Users registered by this user.
     */
    public function registeredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * The warehouse this mandor manages.
     */
    public function managedWarehouse(): HasOne
    {
        return $this->hasOne(\App\Models\Warehouse::class, 'mandor_id');
    }

    // ──────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isKepalaGudang(): bool
    {
        return $this->role === 'kepala_gudang';
    }

    public function isMandor(): bool
    {
        return $this->role === 'mandor';
    }

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    public function isEngineering(): bool
    {
        return $this->role === 'engineering';
    }
}
