<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiIdentitas extends Model
{
    use HasFactory, Auditable;

    protected $table = 'pegawai_identitas';

    protected $fillable = [
        'pegawai_id',
        'id_wos',
        'id_orc',
        'id_sinta',
        'id_scopus',
        'id_garuda',
        'id_gscholar',
        'nidn',
        'nuptk',
        'bidang_penelitian',
        'kelompok_keahlian_id',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function kelompok_keahlian()
    {
        return $this->belongsTo(KelompokKeahlian::class, 'kelompok_keahlian_id');
    }
}
