### Plan: Intelligent HR MVP (Rules-First)
Bangun lapisan "intelligence" berbasis aturan deterministik (tanpa API AI berbayar) di atas modul pegawai, dokumen, dan layanan: skor kelengkapan data, cek kelayakan usulan layanan, dan prioritas antrean proses. Pendekatan ini rendah risiko karena memanfaatkan tabel/flow existing, ditambah service layer, endpoint JSON ringan, job queue database, dan test coverage bertahap agar fitur bisa dirilis per fase tanpa mengganggu operasi harian.
Steps
1. Definisikan arsitektur Intelligence service layer dan kontrak skor di app/Services, integrasikan ke PegawaiController dan DashboardController.
2. Tambahkan model data insight melalui migration baru di database/migrations: pegawai_insights, kolom SLA di layanan_pegawais, metadata verifikasi di dokumen_pegawai.
3. Implementasikan fitur MVP-1 "Profile & Document Completeness Score" pada Pegawai, tampilkan badge/ringkasan di resources/views/pegawai/profile.blade.php dan resources/views/pegawai/dokumen.blade.php.
4. Implementasikan fitur MVP-2 "Layanan Eligibility Precheck" dari relasi Layanan::syarat ke dokumen wajib, tampilkan rekomendasi sebelum submit di resources/views/layanan/create.blade.php.
5. Implementasikan fitur MVP-3 "Smart Triage" untuk layanan_pegawais (priority_score, sla_due_at, sla_risk), urutkan rekap di resources/views/layanan/rekap.blade.php.
6. Tambahkan touchpoint API/UI + async processing: endpoint read-only di routes/api.php, dispatch job di controller upload/usul, schedule recalculation di app/Console/Kernel.php, lalu perluas test di tests/Feature dan tests/Unit.

Further Considerations
1. Pemetaan syarat ke dokumen wajib: Option A tambah dokumen_id di syarats; Option B tabel pivot baru syarat_dokumen; Option C aturan JSON per layanan.
2. Strategi rollout triage: Option A tampilkan rekomendasi saja; Option B auto-sort + manual override; Option C aktifkan SLA warning bertahap per role.
3. Scope API awal: Option A internal JSON untuk Blade only; Option B full Sanctum API publik internal; Option C tunda API dan fokus service+UI dulu.

Here’s the best path for this codebase:
- MVP-1: Intelligent Completeness Score
    - Score each pegawai based on profile fields + required dokumen.
    - Show actionable recommendations: “lengkapi NIDN”, “unggah SK terakhir”, etc.
    - Integrate in pegawai views (resources/views/pegawai/profile.blade.php, resources/views/pegawai/dokumen.blade.php).
- MVP-2: Layanan Eligibility Precheck
  - Before submit usulan, system checks if syarat is complete.
  - Show: “Siap diajukan” or “Belum memenuhi X syarat”.
  - Integrate in resources/views/layanan/create.blade.php.
- MVP-3: Smart Queue for Kepegawaian
  - Add priority_score + SLA risk on layanan_pegawais.
  - Rekap page auto-sorts high-risk/high-priority items first.
  - Integrate in resources/views/layanan/rekap.blade.php.

Recommended technical style:
- Rules-based intelligence first (deterministic, explainable, testable).
- Service layer under app/Services/Intelligence/....
- Async recalculation with queue/jobs and scheduler.
- Strong feature tests + unit tests so behavior stays stable.

### Plan: MVP-1 Score Alignment
Replace hardcoded MVP-1 document scoring with database-driven master-vs-upload reconciliation, then surface a clear per-document ownership status in the UI. This keeps score math tied to real dokumens and dokumen_pegawai data, prevents code mismatch drift, and helps users immediately see which master documents they already own versus still missing.

Steps

1. Audit current scoring flow in app/Services/Intelligence/PegawaiInsightService.php and remove dependency on required_document_codes.
2. Build required-doc source from app/Models/Dokumen.php master records, keyed by dokumen_id and kode_dokumen.
3. Recompute document_score using real uploads in app/Models/DokumenPegawai.php, including owned/missing document collections.
4. Pass normalized ownership payload from PegawaiController::profile and PegawaiController::dokumen in app/Http/Controllers/Front/PegawaiController.php.
5. Update resources/views/pegawai/dokumen.blade.php and resources/views/pegawai/profile.blade.php to show clear “Dimiliki/Belum” status per master doc.
6. Extend tests/Feature/PegawaiIntelligenceFeatureTest.php for score parity against mixed master/upload scenarios.

Further Considerations
1. Should MVP-1 require all master dokumens, or only a flagged subset? Option A all / Option B new is_mvp1_required flag / Option C config fallback.
2. Should draft uploads (status = false) count as owned for scoring? Option A count any upload / Option B count only valid / Option C show both metrics.


# Leave Balance (Jatah Cuti) Rules

## Constants (`App\Services\CutiService`)

| Constant | Value | Meaning |
|---|---|---|
| `HARI_PER_TAHUN` | 12 | Leave days allocated per calendar year |
| `MAX_CARRY_OVER` | 6 | Max days a **previous** year's remainder contributes to the current balance |
| `TAHUN_DIPERHITUNGKAN` | 3 | Number of years rolled into the total (current year + 2 prior years) |

---

## Current Source of Truth

- Workflow/status tetap berada di tabel `layanan_pegawais`.
- Detail cuti tersimpan di tabel dedicated `cuti_layanan_pegawais` melalui relasi `LayananPegawai::cutiDetail()`.
- Perhitungan saldo live dibaca dari `App\Services\CutiService` dengan query `LayananPegawai::whereHas('cutiDetail')`, **bukan** dari kolom legacy `layanan_pegawais.cuti_*` dan **bukan** dari `pegawais.cuti_hari_tersedia`.

---

## Balance Calculation Model

Saldo dihitung dengan simulasi bucket tahunan:

1. setiap tahun mendapatkan jatah `12` hari,
2. sisa tahun sebelumnya dibatasi maksimal `6` hari ketika masuk ke tahun berikutnya,
3. hanya 3 tahun aktif yang diperhitungkan (tahun berjalan + 2 tahun sebelumnya),
4. setiap cuti selesai mengurangi **bucket tahun paling lama yang masih aktif terlebih dahulu**,
5. total saldo adalah penjumlahan sisa seluruh bucket aktif.

Secara konseptual:

```
bucket[tahun] dimulai dari 12 hari

untuk setiap pergantian tahun:
  carry-over bucket lama dibatasi maksimal 6 hari
  bucket yang lebih tua dari jendela 3 tahun dibuang

untuk setiap usulan cuti selesai:
  ambil hari_diminta dari cuti_layanan_pegawais
  konsumsi bucket paling tua yang masih tersedia lebih dulu

total_saldo = jumlah seluruh sisa bucket aktif
```

**Maximum possible total:** 12 (tahun berjalan) + 6 (tahun -1) + 6 (tahun -2) = **24 hari**

---

## Where Each Number Comes From

- **`hari_diminta`** — berasal dari `cuti_layanan_pegawais.hari_diminta`
- **tahun pemakaian** — ditentukan dari `processed_at ?? created_at` pada `layanan_pegawais`
- **`hari_tersedia` / saldo awal** — snapshot bucket aktif pada awal tahun berjalan hasil simulasi `CutiService`
- **`hari_diambil`** — jumlah hari yang benar-benar terpotong dari bucket aktif tahun berjalan selama simulasi
- **`sisa`** — sisa hari pada bucket setelah carry-over cap dan seluruh pemakaian cuti diterapkan

---

## Lifecycle of a Cuti Request

1. **Employee submits date range** → pegawai mengisi `cuti_tanggal_mulai` dan `cuti_tanggal_selesai`.
2. **Working-day calculation** → sistem menghitung `hari_diminta` secara server-side memakai `CutiService::calculateHariKerja()`.
   - hanya **hari kerja** yang dihitung,
   - Sabtu/Minggu dikecualikan,
   - tanggal libur pada `config/cuti.php` juga dikecualikan,
   - config mendukung tanggal exact `Y-m-d` dan recurring `m-d`.
3. **Draft/review** → snapshot saldo saat usul tetap disimpan untuk audit/display.
4. **Persist final usulan** → `layanan_pegawais` dibuat sebagai backbone workflow, lalu detail cuti disimpan ke `cuti_layanan_pegawais`.
5. **Kepegawaian processes** → cuti diproses melalui modul dedicated `kepegawaian.cuti.*`, bukan lagi modul generic layanan.
6. **Status selesai** → saldo tidak menulis balik counter manual ke `pegawais`; saldo selalu dihitung ulang dinamis dari data cuti selesai.
7. **Completed cuti immutable** → usulan cuti yang sudah `selesai` tidak boleh dikembalikan ke status sebelumnya.

---

## Validation Gates

| Gate | Where enforced |
|---|---|
| Rentang tanggal cuti wajib valid | `previewLayananUsulan`, `storeLayananUsulan` |
| Hasil `hari kerja` harus ≥ 1 | `previewLayananUsulan`, `storeLayananUsulan` |
| Requested days ≤ current saldo | `previewLayananUsulan`, `storeLayananUsulan`, `updateLayananProses` |
| All `syarat_uploads` must be `approved` before selesai | `updateLayananProses` |
| Completed cuti status is immutable | `updateLayananProses` |

---

## Legacy Columns

- `pegawais.cuti_hari_tersedia` adalah counter lama dan **tidak** dipakai lagi untuk menghitung saldo live.
- Kolom legacy `layanan_pegawais.cuti_*` juga bukan source of truth runtime.
- Detail operasional cuti sekarang berada di `cuti_layanan_pegawais`.

---

## Maintenance Notes

- For seeded test/demo accounts, see `docs/test-access.md`.
- For Google login / registration setup, see `docs/google-auth-setup.md`.
- `pegawais.jabatan_fungsional` now uses **`profesor`** as the only canonical top-rank value.
- Legacy `guru besar` input is no longer accepted by the application.
- Older databases are normalized by migration `2026_04_13_070000_normalize_profesor_jabatan_fungsional_values`.
- To audit production/staging data for non-canonical raw values, run:

```bash
php artisan audit:jabatan-fungsional-canonical
```

Use this command after deployment or before data cleanup verification. It exits with failure when non-canonical `jabatan_fungsional` values are still present.

## Smart Triage (MVP-3)

Penjelasan lengkap fitur MVP-3 Smart Triage tersedia di:

- `docs/smart-triage.md`

Quick ops:

```bash
php artisan intelligence:refresh-smart-triage
php artisan schedule:work
```
