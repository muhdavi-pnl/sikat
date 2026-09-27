<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory, Auditable;

    public const JENIS_KEPEGAWAIAN = 'kepegawaian';
    public const JENIS_FUNGSIONAL = 'fungsional';
    public const JENIS_CUTI = 'cuti';

    public const JENIS_OPTIONS = [
        self::JENIS_CUTI => 'Cuti',
        self::JENIS_FUNGSIONAL => 'Fungsional',
        self::JENIS_KEPEGAWAIAN => 'Kepegawaian',
    ];

    protected $fillable = [
        'layanan',
        'deskripsi',
        'jenis',
    ];

    public function syarat()
    {
        return $this->belongsToMany(Syarat::class);
    }

    public function layananPegawais()
    {
        return $this->hasMany(LayananPegawai::class);
    }
}
