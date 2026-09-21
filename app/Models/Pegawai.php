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
        'no_serdos',
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
        'no_serdos',
        'bidang_penelitian',
        'kelompok_keahlian_id',
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
        'no_serdos',
        'bidang_penelitian',
        'kelompok_keahlian_id',
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
        'tmt_pangkat',
        'tmt_cpns',
        'tmt_pns',
        'tmt_jabatan',
        'tmt_pmk',
        'pmk_tahun',
        'pmk_bulan',
        'email',
        'no_hp',
        'no_telp',
        'alamat_asal',
        'kelurahan_asal_id',
        'alamat',
        'kelurahan_id',
        'eselon_id',
        'kedudukan_pegawai_id',
        'agama_id',
        'jenis_jabatan_id',
        'jabatan_id',
        'jabatan_rangkap_id',
        'pangkat_id',
        'status_perkawinan_id',
        'pendidikan_id',
        'program_studi_id',
        'unit_kerja_id',
        'user_id',
        'jabatan_fungsional',
        'status_pegawai',
        'cuti_hari_tersedia',
        'kelompok_pegawai',
    ];

    protected $attributes = [
        'kelompok_pegawai' => 'dosen',
        'status_pegawai' => 'PNS',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_lulus' => 'date',
        'tmt_pangkat' => 'date',
        'tmt_cpns' => 'date',
        'tmt_pns' => 'date',
        'tmt_jabatan' => 'date',
        'tmt_pmk' => 'date',
        'pmk_tahun' => 'integer',
        'pmk_bulan' => 'integer',
        'jenis_kelamin' => 'boolean',
        'cuti_hari_tersedia' => 'integer',
        'kelompok_keahlian_id' => 'integer',
    ];

    protected $appends = [
        'nama_lengkap',
        'nama_tanpa_gelar',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $pegawai): void {
            $pegawai->syncIdentityAttributes();
        });
    }

    /**
     * Format nama pegawai beserta gelar depan dan gelar belakang.
     */
    public static function formatNamaPegawai(?string $nama, ?string $gelarDepan = null, ?string $gelarBelakang = null): string
    {
        $cleanNama = trim((string) ($nama ?? ''));
        if ($cleanNama === '') {
            return '';
        }

        $front = trim((string) ($gelarDepan ?? ''));
        $back = trim((string) ($gelarBelakang ?? ''));

        $formatted = $front !== '' ? $front . ' ' . $cleanNama : $cleanNama;

        if ($back !== '') {
            $back = ltrim($back, ', ');
            $formatted .= ', ' . $back;
        }

        return $formatted;
    }

    /**
     * Accessor untuk nama lengkap beserta gelar depan dan belakang.
     */
    public function getNamaLengkapAttribute(): string
    {
        return static::formatNamaPegawai(
            $this->nama,
            $this->gelar_depan,
            $this->gelar_belakang
        );
    }

    /**
     * Accessor alias untuk nama lengkap dengan gelar.
     */
    public function getNamaDenganGelarAttribute(): string
    {
        return $this->nama_lengkap;
    }

    /**
     * Accessor untuk nama tanpa gelar (hanya nama asli).
     */
    public function getNamaTanpaGelarAttribute(): string
    {
        return trim((string) ($this->nama ?? ''));
    }

    /**
     * Masked PII Accessors untuk perlindungan data pribadi (UU PDP No. 27/2022).
     */
    public function getMaskedNikAttribute(): string
    {
        return app(\App\Services\Security\PegawaiDataProtectionService::class)->maskNik($this->nik);
    }

    public function getMaskedNpwpAttribute(): string
    {
        return app(\App\Services\Security\PegawaiDataProtectionService::class)->maskNpwp($this->npwp);
    }

    public function getMaskedBpjsAttribute(): string
    {
        return app(\App\Services\Security\PegawaiDataProtectionService::class)->maskBpjs($this->bpjs);
    }

    public function getMaskedNoHpAttribute(): string
    {
        return app(\App\Services\Security\PegawaiDataProtectionService::class)->maskPhone($this->no_hp);
    }

    public function getMaskedNoTelpAttribute(): string
    {
        return app(\App\Services\Security\PegawaiDataProtectionService::class)->maskPhone($this->no_telp);
    }

    public function getMaskedEmailAttribute(): string
    {
        return app(\App\Services\Security\PegawaiDataProtectionService::class)->maskEmail($this->email);
    }

    public function getMaskedAlamatAttribute(): string
    {
        return app(\App\Services\Security\PegawaiDataProtectionService::class)->maskAlamat($this->alamat);
    }

    public function getMaskedAlamatAsalAttribute(): string
    {
        return app(\App\Services\Security\PegawaiDataProtectionService::class)->maskAlamat($this->alamat_asal);
    }

    /**
     * Mengecek apakah user yang diberikan berhak melihat data unmasked.
     */
    public function canViewSensitiveData(?\App\Models\User $user = null): bool
    {
        $user = $user ?? auth()->user();
        return app(\App\Services\Security\PegawaiDataProtectionService::class)->canViewUnmaskedData($user, $this);
    }

    /**
     * Helper method untuk mengambil nama dengan atau tanpa gelar.
     */
    public function formatNama(bool $withGelar = true): string
    {
        return $withGelar ? $this->nama_lengkap : $this->nama_tanpa_gelar;
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

    protected $tempJabatanFungsional = null;

    public function getJabatanFungsionalAttribute($value = null): ?string
    {
        return self::normalizeJabatanFungsional($this->tempJabatanFungsional ?? $value ?? ($this->jabatan?->jabatan ?? null));
    }

    public function setJabatanFungsionalAttribute($value): void
    {
        $normalized = self::normalizeJabatanFungsional($value);
        $this->tempJabatanFungsional = $normalized;
        unset($this->attributes['jabatan_fungsional']);

        if ($normalized) {
            $label = self::JABATAN_FUNGSIONAL_OPTIONS[$normalized] ?? ucwords($normalized);
            $matchingJabatan = Jabatan::whereRaw('LOWER(jabatan) = ?', [$normalized])
                ->orWhereRaw('LOWER(jabatan) = ?', [mb_strtolower($label)])
                ->first();

            if ($matchingJabatan) {
                $this->attributes['jabatan_id'] = $matchingJabatan->id;
            } else {
                $created = Jabatan::create(['jabatan' => $label]);
                $this->attributes['jabatan_id'] = $created->id;
            }
        }
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

    public function getNoSerdosAttribute(): ?string
    {
        return $this->identitas?->no_serdos ?? $this->attributes['no_serdos'] ?? null;
    }

    public function setNoSerdosAttribute($value): void
    {
        $this->setIdentityAttributeValue('no_serdos', $value);
    }

    public function getBidangPenelitianAttribute(): ?string
    {
        return $this->identitas?->bidang_penelitian ?? $this->attributes['bidang_penelitian'] ?? null;
    }

    public function setBidangPenelitianAttribute($value): void
    {
        $this->setIdentityAttributeValue('bidang_penelitian', $value);
    }

    public function getKelompokKeahlianIdAttribute(): ?int
    {
        $val = $this->identitas?->kelompok_keahlian_id ?? $this->attributes['kelompok_keahlian_id'] ?? null;
        return $val !== null ? (int) $val : null;
    }

    public function setKelompokKeahlianIdAttribute($value): void
    {
        $this->setIdentityAttributeValue('kelompok_keahlian_id', $value);
    }

    public function kelompok_keahlian()
    {
        return $this->belongsTo(KelompokKeahlian::class, 'kelompok_keahlian_id');
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

    public function studiLanjuts()
    {
        return $this->hasMany(StudiLanjut::class, 'pegawai_id');
    }

    public function latestStudiLanjut()
    {
        return $this->hasOne(StudiLanjut::class, 'pegawai_id')->latestOfMany();
    }

    public function program_studi()
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function jenis_jabatan()
    {
        return $this->belongsTo(JenisJabatan::class, 'jenis_jabatan_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function jabatan_rangkap()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_rangkap_id');
    }

    public function jabatan_struktural()
    {
        return $this->jabatan_rangkap();
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

    public function kelurahan_asal()
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_asal_id');
    }

    public function kelurahan_domisili()
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id');
    }

    public function cutiQuotas()
    {
        return $this->hasMany(PegawaiCutiQuota::class, 'pegawai_id')->orderBy('tahun', 'desc');
    }

    public function getCutiQuotaForYear(int $year): int
    {
        $quota = $this->relationLoaded('cutiQuotas')
            ? $this->cutiQuotas->firstWhere('tahun', $year)
            : $this->cutiQuotas()->firstWhere('tahun', $year);

        return $quota ? (int) $quota->hari_tersedia : (int) ($this->cuti_hari_tersedia ?? \App\Services\CutiService::HARI_PER_TAHUN);
    }

    public function getAlamatDomisiliAttribute(): ?string
    {
        return $this->alamat;
    }

    public function isTendik(): bool
    {
        $kelompok = strtolower(trim((string) $this->kelompok_pegawai));

        return in_array($kelompok, ['tendik', 'tenaga kependidikan'], true);
    }

    public function isDosen(): bool
    {
        return ! $this->isTendik();
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
