<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LayananPegawai extends Model
{
	use HasFactory, Auditable;

	public const STATUS_USULAN = 'usulan';
	public const STATUS_PENDING = 'pending';
	public const STATUS_PROSES = 'proses';
	public const STATUS_SELESAI = 'selesai';
	public const STATUS_DITOLAK = 'ditolak';

	protected $fillable = [
		'layanan_id',
		'pegawai_id',
		'user_id',
		'status',
		'priority_score',
		'sla_due_at',
		'sla_risk',
		'catatan_pengusul',
		'syarat_uploads',
		'output_file',
		'output_original_name',
		'output_path',
		'catatan_proses',
		'processed_by',
		'processed_at',
		'output_uploaded_by',
		'output_uploaded_at',
	];

	protected $casts = [
		'cuti_hari_diminta' => 'integer',
		'cuti_hari_tersedia' => 'integer',
		'cuti_tanggal_mulai' => 'date',
		'cuti_tanggal_selesai' => 'date',
		'priority_score' => 'integer',
		'sla_due_at' => 'datetime',
		'syarat_uploads' => 'array',
		'processed_at' => 'datetime',
		'output_uploaded_at' => 'datetime',
	];

	public function layanan()
	{
		return $this->belongsTo(Layanan::class);
	}

	public function pegawai()
	{
		return $this->belongsTo(Pegawai::class);
	}

	public function cutiDetail()
	{
		return $this->hasOne(CutiLayananPegawai::class, 'layanan_pegawai_id');
	}

	public function pengusul()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function processor()
	{
		return $this->belongsTo(User::class, 'processed_by');
	}

	public function outputUploader()
	{
		return $this->belongsTo(User::class, 'output_uploaded_by');
	}

	public static function statusOptions(): array
	{
		return [
			self::STATUS_USULAN => 'Usulan',
			self::STATUS_PENDING => 'Menunggu',
			self::STATUS_PROSES => 'Proses',
			self::STATUS_SELESAI => 'Selesai',
			self::STATUS_DITOLAK => 'Ditolak',
		];
	}

	public static function statusBadgeClass(?string $status): string
	{
		return [
			self::STATUS_USULAN => 'badge-primary',
			self::STATUS_PENDING => 'badge-warning',
			self::STATUS_PROSES => 'badge-info',
			self::STATUS_SELESAI => 'badge-success',
			self::STATUS_DITOLAK => 'badge-danger',
		][$status ?? ''] ?? 'badge-secondary';
	}

	public static function slaRiskLabel(?string $risk): string
	{
		return [
			'high' => 'Risiko Tinggi',
			'medium' => 'Risiko Sedang',
			'low' => 'Risiko Rendah',
			'none' => 'Tidak Berlaku',
		][$risk ?? ''] ?? 'Tidak Berlaku';
	}

	public static function slaRiskBadgeClass(?string $risk): string
	{
		return [
			'high' => 'badge-danger',
			'medium' => 'badge-warning',
			'low' => 'badge-success',
			'none' => 'badge-secondary',
		][$risk ?? ''] ?? 'badge-secondary';
	}

	public function getCutiHariDimintaAttribute($value)
	{
		$cutiDetail = $this->resolvedCutiDetail();
		if ($cutiDetail) {
			return $cutiDetail->hari_diminta;
		}

		return $value;
	}

	public function getCutiHariTersediaAttribute($value)
	{
		$cutiDetail = $this->resolvedCutiDetail();
		if ($cutiDetail) {
			return $cutiDetail->hari_tersedia_saat_usul;
		}

		return $value;
	}

	public function getCutiTanggalMulaiAttribute($value)
	{
		$cutiDetail = $this->resolvedCutiDetail();
		if ($cutiDetail) {
			return $cutiDetail->tanggal_mulai;
		}

		return $value ? Carbon::parse($value) : null;
	}

	public function getCutiTanggalSelesaiAttribute($value)
	{
		$cutiDetail = $this->resolvedCutiDetail();
		if ($cutiDetail) {
			return $cutiDetail->tanggal_selesai;
		}

		return $value ? Carbon::parse($value) : null;
	}

	public function hasDedicatedCutiDetail(): bool
	{
		return $this->resolvedCutiDetail() !== null;
	}

	public function resolvedCutiDetail(): ?CutiLayananPegawai
	{
		return $this->resolveCutiDetail();
	}

	protected function resolveCutiDetail(): ?CutiLayananPegawai
	{
		if ($this->relationLoaded('cutiDetail')) {
			return $this->getRelation('cutiDetail');
		}

		if (!$this->exists) {
			return null;
		}

		$cutiDetail = $this->cutiDetail()->first();
		$this->setRelation('cutiDetail', $cutiDetail);

		return $cutiDetail;
	}
}


