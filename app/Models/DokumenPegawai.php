<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenPegawai extends Model
{
    use HasFactory, Auditable;

    public const STATUS_PENDING = 0;
    public const STATUS_VALID = 1;
    public const STATUS_REJECTED = 2;

    protected $table = 'dokumen_pegawai';

    protected $fillable = [
        'dokumen_id',
        'pegawai_id',
        'user_id',
        'file',
        'nomor',
        'tanggal',
        'status',
        'keterangan',
        'alasan_penolakan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'status' => 'integer',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function isValid(): bool
    {
        return (int) $this->status === self::STATUS_VALID;
    }

    public function isRejected(): bool
    {
        return (int) $this->status === self::STATUS_REJECTED;
    }

    public function isPending(): bool
    {
        return (int) $this->status === self::STATUS_PENDING;
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_VALID => 'Valid',
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_REJECTED => 'Ditolak',
        ];
    }

    public static function statusLabel($status): string
    {
        return match ((int) $status) {
            self::STATUS_VALID => 'Valid',
            self::STATUS_REJECTED => 'Ditolak',
            default => 'Menunggu Verifikasi',
        };
    }

    public static function statusBadgeClass($status): string
    {
        return match ((int) $status) {
            self::STATUS_VALID => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            default => 'badge-warning',
        };
    }
}
