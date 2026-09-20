<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Auditable;
    use HasRoles; # from spatie laravel permission

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'google_id',
        'password',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class);
    }

    public function layananDiusulkan()
    {
        return $this->hasMany(LayananPegawai::class, 'user_id');
    }

    public function layananDiproses()
    {
        return $this->hasMany(LayananPegawai::class, 'processed_by');
    }

    /**
     * Accessor nama lengkap pengguna (menggunakan nama lengkap pegawai jika terhubung).
     */
    public function getNamaLengkapAttribute(): string
    {
        return $this->pegawai?->nama_lengkap ?? (string) ($this->name ?? '');
    }

    /**
     * Accessor alias untuk nama dengan gelar.
     */
    public function getNamaDenganGelarAttribute(): string
    {
        return $this->nama_lengkap;
    }

    /**
     * Accessor nama tanpa gelar pengguna (menggunakan nama tanpa gelar pegawai jika terhubung).
     */
    public function getNamaTanpaGelarAttribute(): string
    {
        return $this->pegawai?->nama_tanpa_gelar ?? (string) ($this->name ?? '');
    }

    /**
     * Helper method untuk mengambil nama pengguna dengan atau tanpa gelar.
     */
    public function formatNama(bool $withGelar = true): string
    {
        return $withGelar ? $this->nama_lengkap : $this->nama_tanpa_gelar;
    }
}
