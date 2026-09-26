<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    use HasFactory, Auditable;

    public $timestamps = false;

    protected $fillable = [
        'unit_kerja',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }

    public function getKodeAttribute(): string
    {
        return self::formatKodeUnitKerja($this->unit_kerja);
    }

    public static function formatKodeUnitKerja(?string $name): string
    {
        if (empty($name)) {
            return 'UMUM';
        }

        $map = [
            'Politeknik Negeri Lhokseumawe' => 'PNL',
            'Jurusan Teknologi Informasi dan Komputer' => 'JTIK',
            'Jurusan Teknik Sipil' => 'JTS',
            'Jurusan Teknik Kimia' => 'JTK',
            'Jurusan Teknik Mesin' => 'JTM',
            'Jurusan Teknik Elektro' => 'JTE',
            'Jurusan Bisnis' => 'JBN',
            'Bagian Akademik, Kemahasiswaan, dan Alumni' => 'BAKA',
            'Bagian Perencanaan, Keuangan, dan Umum' => 'BPKU',
            'Subbagian Akademik' => 'SBA',
            'Subbagian Umum' => 'SBU',
            'Pusat Penelitian dan Pengabdian Kepada Masyarakat' => 'P3M',
            'Pusat Penjaminan Mutu dan Pengembangan Pembelajaran' => 'P4M',
            'UPA Perpustakaan' => 'UPA-PERPUS',
            'UPA Teknologi Informasi dan Komunikasi' => 'UPATIK',
            'UPA Bahasa' => 'UPABAHASA',
            'UPA Perawatan dan Perbaikan' => 'UPAPP',
            'UPA Layanan Uji Kompetensi' => 'UPALUK',
            'UPA Pengembangan Karir dan Kemahasiswaan' => 'UPAPKK',
            'UPA Pengembangan Teknologi dan Produk Unggulan' => 'UPAPTPU',
            'Bidang Akademik dan Sistem Informasi' => 'BAKSI',
            'Bidang Keuangan dan Umum' => 'BKU',
            'Bidang Kemahasiswaan dan Alumni' => 'BKA',
            'Bidang Perencanaan dan Kerja Sama' => 'BPKS',
            'Senat' => 'SENAT',
            'Satuan Pengawas Internal' => 'SPI',
        ];

        foreach ($map as $key => $code) {
            if (strcasecmp(trim($name), $key) === 0 || str_contains(strtolower($name), strtolower($key))) {
                return $code;
            }
        }

        $stopWords = ['dan', 'yang', 'di', 'ke', 'dari', 'untuk', 'kepada', 'pada', 'dengan', 'atas', 'jurusan', 'bagian'];
        $words = preg_split('/\s+/', preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
        $words = array_values(array_filter($words, fn ($w) => !in_array(strtolower($w), $stopWords) && strlen($w) > 0));

        if (empty($words)) {
            return 'UNIT';
        }

        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 5));
        }

        $acronym = '';
        foreach ($words as $w) {
            $acronym .= strtoupper(substr($w, 0, 1));
        }

        return $acronym ?: 'UNIT';
    }
}
