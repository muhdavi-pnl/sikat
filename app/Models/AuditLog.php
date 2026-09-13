<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'role',
        'event_type',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'status',
        'properties',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForEvent(Builder $query, string $event): Builder
    {
        return $query->where('event_type', $event);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByIp(Builder $query, string $ip): Builder
    {
        return $query->where('ip_address', 'like', "%{$ip}%");
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return match ($category) {
            'auth' => $query->where('event_type', 'like', 'auth.%'),
            'model' => $query->where('event_type', 'like', 'model.%'),
            'security' => $query->where(function ($q) {
                $q->where('event_type', 'like', 'security.%')
                    ->orWhere('event_type', 'auth.lockout')
                    ->orWhere('event_type', 'auth.login_failed');
            }),
            default => $query,
        };
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $sub) use ($term) {
            $sub->where('action', 'like', "%{$term}%")
                ->orWhere('user_name', 'like', "%{$term}%")
                ->orWhere('user_email', 'like', "%{$term}%")
                ->orWhere('event_type', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%")
                ->orWhere('role', 'like', "%{$term}%");
        });
    }

    public function scopeDateBetween(Builder $query, ?string $start, ?string $end): Builder
    {
        if (! empty($start)) {
            $query->where('created_at', '>=', $start . ' 00:00:00');
        }

        if (! empty($end)) {
            $query->where('created_at', '<=', $end . ' 23:59:59');
        }

        return $query;
    }

    public function getCategoryAttribute(): string
    {
        if (str_starts_with($this->event_type, 'auth.')) {
            return 'Autentikasi';
        }

        if (str_starts_with($this->event_type, 'model.')) {
            return 'Database';
        }

        if (str_starts_with($this->event_type, 'security.')) {
            return 'Keamanan';
        }

        return 'Sistem';
    }

    public function getEventBadgeClassAttribute(): string
    {
        return match ($this->event_type) {
            'auth.login' => 'badge-success',
            'auth.login_failed' => 'badge-danger',
            'auth.logout' => 'badge-secondary',
            'auth.registered' => 'badge-info',
            'auth.lockout' => 'badge-danger',
            'auth.password_changed', 'auth.password_reset' => 'badge-warning',
            'model.created' => 'badge-primary',
            'model.updated' => 'badge-info',
            'model.deleted', 'model.force_deleted' => 'badge-danger',
            'model.restored' => 'badge-success',
            default => 'badge-dark',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'success' => 'badge-success',
            'failed' => 'badge-danger',
            'warning' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}
