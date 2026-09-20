<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    use HasFactory, Auditable;

    protected $table = 'pengumumans';

    protected $fillable = [
        'judul',
        'tipe',
        'isi',
        'gambar',
        'is_aktif',
        'target_role',
        'tanggal_mulai',
        'tanggal_selesai',
        'created_by',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope query to only include currently active announcements.
     */
    public function scopeAktif(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_aktif', true)
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('tanggal_mulai')
                    ->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $today);
            });
    }

    /**
     * Scope query for audience / user role.
     */
    public function scopeForUser(Builder $query, ?User $user = null): Builder
    {
        if (! $user) {
            return $query->where(function (Builder $q) {
                $q->whereNull('target_role')
                    ->orWhere('target_role', 'semua');
            });
        }

        $roles = $user->roles->pluck('name')->all();

        return $query->where(function (Builder $q) use ($roles) {
            $q->whereNull('target_role')
                ->orWhere('target_role', 'semua')
                ->orWhereIn('target_role', $roles);
        });
    }

    /**
     * Get URL for the announcement image.
     */
    public function getGambarUrlAttribute(): ?string
    {
        if (! $this->gambar) {
            return null;
        }

        if (str_starts_with($this->gambar, 'http://') || str_starts_with($this->gambar, 'https://')) {
            return $this->gambar;
        }

        return asset($this->gambar);
    }
}
