<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PejabatCutiSetting extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'pegawai_id',
        'jabatan_label',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getActivePybmc(): ?self
    {
        return self::with(['pegawai.jabatan', 'pegawai.unit_kerja'])
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }
}
