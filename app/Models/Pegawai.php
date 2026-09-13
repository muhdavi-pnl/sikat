<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pegawai extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $pendingIdentityAttributes = [];

    public const EXTERNAL_IDENTIFIER_FIELDS = [
        'id_wos',
        'id_orc',
        'id_sinta',
        'id_scopus',
        'id_garuda',
        'id_gscholar',
        'nidn',
        'nuptk',
    ];

    public const IDENTITY_FIELDS = [
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

    public const JABATAN_FUNGSIONAL_OPTIONS = [
        'asisten ahli' => 'Asisten Ahli',
        'lektor' => 'Lektor',
        'lektor kepala' => 'Lektor Kepala',
        'profesor' => 'Profesor',
    ];

    protected $fillable = [
        'id_wos',
        'id_orc',
        'id_sinta',
        'id_scopus',
        'id_garuda',
        'id_gscholar',
        'nidn',
        'nuptk',
        'nip',
        'nik',
        'nama',
        'gelar_depan',
        'gelar_belakang',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'jumlah_anak',
        'tanggal_lulus',
        'npwp',
        'bpjs',
        'no_karpeg',
        'no_karis_karsu',
        'tmt_cpns',
        'tmt_pns',
        'tmt_jabatan',
        'email',
        'no_hp',
        'no_telp',
        'alamat',
        'kelurahan_id',
        'eselon_id',
        'kedudukan_pegawai_id',
        'agama_id',
        'jabatan_id',
        'pangkat_id',
        'status_perkawinan_id',
        'pendidikan_id',
        'program_studi_id',
        'unit_kerja_id',
        'user_id',
        'jabatan_fungsional',
        'status_pegawai',
        'cuti_hari_tersedia',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_lulus' => 'date',
        'tmt_cpns' => 'date',
        'tmt_pns' => 'date',
        'tmt_jabatan' => 'date',
        'jenis_kelamin' => 'boolean',
        'cuti_hari_tersedia' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $pegawai): void {
            $pegawai->syncIdentityAttributes();
        });
    }

    public static function jabatanFungsionalOptions(): array
    {
        return self::JABATAN_FUNGSIONAL_OPTIONS;
    }

    public static function jabatanFungsionalValidationValues(): array
    {
        return array_keys(self::JABATAN_FUNGSIONAL_OPTIONS);
    }

    public static function normalizeJabatanFungsional($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = mb_strtolower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        return $normalized;
    }

    public static function jabatanFungsionalLabel($value, string $default = '-'): string
    {
        $normalized = self::normalizeJabatanFungsional($value);

        if ($normalized === null) {
            return $default;
        }

        return self::JABATAN_FUNGSIONAL_OPTIONS[$normalized] ?? ucwords($normalized);
    }

    public function getJabatanFungsionalAttribute($value): ?string
    {
        return self::normalizeJabatanFungsional($this->identitas?->jabatan_fungsional ?? $value);
    }

    public function setJabatanFungsionalAttribute($value): void
    {
        $this->setIdentityAttributeValue('jabatan_fungsional', $value, true);
    }

    public function pendidikan()
    {
        return $this->belongsTo(Pendidikan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function identitas()
    {
        return $this->hasOne(PegawaiIdentitas::class, 'pegawai_id');
    }

    public function getIdWosAttribute(): ?string
    {
        return $this->identitas?->id_wos ?? $this->attributes['id_wos'] ?? null;
    }

    public function setIdWosAttribute($value): void
    {
        $this->setIdentityAttributeValue('id_wos', $value);
    }

    public function getIdOrcAttribute(): ?string
    {
        return $this->identitas?->id_orc ?? $this->attributes['id_orc'] ?? null;
    }

    public function setIdOrcAttribute($value): void
    {
        $this->setIdentityAttributeValue('id_orc', $value);
    }

    public function getIdSintaAttribute(): ?string
    {
        return $this->identitas?->id_sinta ?? $this->attributes['id_sinta'] ?? null;
    }

    public function setIdSintaAttribute($value): void
    {
        $this->setIdentityAttributeValue('id_sinta', $value);
    }

    public function getIdScopusAttribute(): ?string
    {
        return $this->identitas?->id_scopus ?? $this->attributes['id_scopus'] ?? null;
    }

    public function setIdScopusAttribute($value): void
    {
        $this->setIdentityAttributeValue('id_scopus', $value);
    }

    public function getIdGarudaAttribute(): ?string
    {
        return $this->identitas?->id_garuda ?? $this->attributes['id_garuda'] ?? null;
    }

    public function setIdGarudaAttribute($value): void
    {
        $this->setIdentityAttributeValue('id_garuda', $value);
    }

    public function getIdGscholarAttribute(): ?string
    {
        return $this->identitas?->id_gscholar ?? $this->attributes['id_gscholar'] ?? null;
    }

    public function setIdGscholarAttribute($value): void
    {
        $this->setIdentityAttributeValue('id_gscholar', $value);
    }

    public function getNidnAttribute(): ?string
    {
        return $this->identitas?->nidn ?? $this->attributes['nidn'] ?? null;
    }

    public function setNidnAttribute($value): void
    {
        $this->setIdentityAttributeValue('nidn', $value);
    }

    public function getNuptkAttribute(): ?string
    {
        return $this->identitas?->nuptk ?? $this->attributes['nuptk'] ?? null;
    }

    public function setNuptkAttribute($value): void
    {
        $this->setIdentityAttributeValue('nuptk', $value);
    }

    public function dokumen()
    {
        return $this->belongsToMany(Dokumen::class)->withPivot(['file', 'nomor', 'tanggal', 'status', 'keterangan']);
    }

    public function dokumenPegawais()
    {
        return $this->hasMany(DokumenPegawai::class);
    }

    public function lokasiArsips()
    {
        return $this->hasMany(LokasiArsip::class);
    }

    public function layananPegawais()
    {
        return $this->hasMany(LayananPegawai::class);
    }

    public function cutiLayananPegawais()
    {
        return $this->hasManyThrough(
            CutiLayananPegawai::class,
            LayananPegawai::class,
            'pegawai_id',
            'layanan_pegawai_id'
        );
    }

    public function program_studi()
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function unit_kerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function pangkat()
    {
        return $this->belongsTo(Pangkat::class);
    }

    public function agama()
    {
        return $this->belongsTo(Agama::class);
    }

    public function status_perkawinan()
    {
        return $this->belongsTo(StatusPerkawinan::class);
    }

    public function eselon()
    {
        return $this->belongsTo(Eselon::class);
    }

    public function kedudukan_pegawai()
    {
        return $this->belongsTo(KedudukanPegawai::class, 'kedudukan_pegawai_id');
    }

    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class);
    }

    protected function setIdentityAttributeValue(string $key, $value, bool $normalizeJabatanFungsional = false): void
    {
        $this->pendingIdentityAttributes[$key] = $normalizeJabatanFungsional
            ? self::normalizeJabatanFungsional($value)
            : self::normalizeIdentityAttribute($value);

        unset($this->attributes[$key]);
    }

    protected static function normalizeIdentityAttribute($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    protected function syncIdentityAttributes(): void
    {
        if ($this->pendingIdentityAttributes === []) {
            return;
        }

        $payload = $this->pendingIdentityAttributes;
        $this->pendingIdentityAttributes = [];

        $identitas = $this->relationLoaded('identitas')
            ? $this->getRelation('identitas')
            : $this->identitas()->first();

        if ($identitas) {
            $identitas->fill($payload)->save();
            $this->setRelation('identitas', $identitas->fresh());

            return;
        }

        $hasAnyValue = collect($payload)->contains(function ($value) {
            return $value !== null;
        });

        if (!$hasAnyValue) {
            return;
        }

        $this->setRelation('identitas', $this->identitas()->create($payload));
    }
}
