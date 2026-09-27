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
        'kode',
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
        if (!empty($this->attributes['kode'])) {
            return $this->attributes['kode'];
        }

        return self::formatKodeUnitKerja($this->unit_kerja);
    }

    public static function formatKodeUnitKerja(?string $name): string
    {
        if (empty($name)) {
            return 'UMUM';
        }

        $trimmed = trim($name);

        // Check database first
        try {
            $fromDb = self::where('unit_kerja', $trimmed)
                ->orWhere('unit_kerja', 'like', '%' . $trimmed . '%')
                ->value('kode');

            if (!empty($fromDb)) {
                return $fromDb;
            }
        } catch (\Throwable) {
            // In case table or column is not yet migrated in an isolated environment
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
