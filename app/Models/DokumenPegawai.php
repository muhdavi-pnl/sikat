<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenPegawai extends Model
{
    use HasFactory, Auditable;

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
    ];

    protected $casts = [
        'tanggal' => 'date',
        'status' => 'boolean',
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

    public static function statusLabel(bool $status): string
    {
        return $status ? 'Valid' : 'Menunggu Verifikasi';
    }

    public static function statusBadgeClass(bool $status): string
    {
        return $status ? 'badge-success' : 'badge-warning';
    }
}
