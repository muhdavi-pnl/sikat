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
        'jabatan_fungsional',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}
