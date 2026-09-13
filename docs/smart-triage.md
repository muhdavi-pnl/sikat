# MVP-3 Smart Triage (Layanan Kepegawaian)

Dokumen ini menjelaskan fitur MVP-3 **Smart Triage** (kadang tertulis "smart tiage") yang dipakai untuk memprioritaskan antrean layanan kepegawaian secara deterministik (rules-based).

## Tujuan

- Mengurutkan antrean proses agar item paling berisiko melanggar SLA muncul paling atas.
- Memberi skor prioritas yang transparan dan mudah diaudit.
- Menjaga konsistensi prioritas lewat refresh otomatis (event controller + scheduler + command).

## Data Model

Fitur ini menambah 3 kolom pada `layanan_pegawais`:

- `priority_score` (`0-100`) -> skor prioritas gabungan.
- `sla_due_at` (`timestamp nullable`) -> batas waktu SLA berdasarkan status.
- `sla_risk` (`high|medium|low|none`) -> tingkat risiko terhadap `sla_due_at`.

Migration: `database/migrations/2026_05_09_000000_add_smart_triage_fields_to_layanan_pegawais_table.php`.

## Cara Kerja Inti

Implementasi utama ada di `app/Services/Intelligence/SmartTriageService.php`.

### 1) Hitung `priority_score`

Rumus saat ini:

```text
priority_score = clamp(0..100, base_score_by_status + age_bonus + keyword_bonus)
```

Komponen:

- **base_score_by_status** dari `config/intelligence.php` -> `triage.status_base_scores`.
- **age_bonus** dari umur antrean (jam) -> `triage.age.hours_per_point` dan `triage.age.max_bonus`.
- **keyword_bonus** jika `catatan_pengusul` mengandung kata mendesak (`triage.urgent_keywords`).

Default konfigurasi:

- Status: `usulan=70`, `pending=60`, `proses=50`, `selesai=5`, `ditolak=0`.
- Bonus umur: `+1` poin per `6` jam (maks `20`).
- Bonus keyword mendesak: `+10`.

### 2) Hitung `sla_due_at`

- Jika status terminal (`selesai`, `ditolak`), maka `sla_due_at = null` dan `sla_risk = none`.
- Jika belum terminal, SLA dihitung dari `created_at + sla_hours_by_status`.

Default `sla_hours`:

- `usulan=24`
- `pending=24`
- `proses=72`
- `default=48`

### 3) Hitung `sla_risk`

Berdasarkan sisa menit menuju `sla_due_at`:

- `high`: sudah lewat SLA atau <= `high_minutes` (default `360` menit / 6 jam)
- `medium`: <= `medium_minutes` (default `1440` menit / 24 jam)
- `low`: sisanya masih longgar
- `none`: untuk status terminal

## Kapan Nilai Triage Direfresh

### Sinkron (langsung saat flow utama)

- Setelah usulan dibuat: `PegawaiController::storeLayananUsulan`
- Setelah proses/status diupdate: `PegawaiController::updateLayananProses`
- Saat halaman proses dibuka (batch refresh sebelum listing): `PegawaiController::indexLayananProses`

### Asinkron (batch periodik)

- Command manual:

```bash
php artisan intelligence:refresh-smart-triage
php artisan intelligence:refresh-smart-triage --include-cuti
```

- Scheduler otomatis di `app/Console/Kernel.php`:
  - `intelligence:refresh-smart-triage` setiap 30 menit (`withoutOverlapping()`).

## Aturan Urutan Antrean

Sorting yang dipakai UI/API:

1. `sla_risk`: `high` -> `medium` -> `low` -> `none`
2. `priority_score`: terbesar ke terkecil
3. `sla_due_at`: yang paling dekat dulu
4. fallback `created_at` terbaru

Tujuannya: item yang paling kritikal tampil lebih dulu, lalu dibedakan lagi oleh bobot prioritas.

## API Read-Only

Endpoint internal:

- `GET /api/intelligence/layanan-triage`
- Route name: `api.intelligence.layanan-triage.index`
- Middleware: `auth:sanctum` + role `super-admin|kepegawaian`

Query params:

- `q` -> cari nama pegawai / NIP / nama layanan
- `risk` -> filter `high|medium|low|none`
- `per_page` -> pagination (`1..100`)
- `refresh=1` -> paksa refresh triage sebelum data dipaginasi

Contoh:

```bash
curl -H "Authorization: Bearer <SANCTUM_TOKEN>" \
  "http://localhost/api/intelligence/layanan-triage?risk=high&refresh=1&per_page=20"
```

Contoh ringkas response:

```json
{
  "data": [
    {
      "id": 123,
      "status": "usulan",
      "priority_score": 86,
      "sla_due_at": "2026-05-10T06:30:00+07:00",
      "sla_risk": "high",
      "pegawai": { "id": 5, "nama": "A", "nip": "1985..." },
      "layanan": { "id": 2, "nama": "Mutasi Internal" }
    }
  ],
  "meta": {
    "refreshed_count": 20,
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 45
  }
}
```

## Pengujian

Cakupan test utama:

- `tests/Feature/LayananFeatureTest.php` (sorting + kolom triage di UI)
- `tests/Feature/SmartTriageApiFeatureTest.php` (API triage + urutan hasil)
- `tests/Feature/RefreshSmartTriageCommandTest.php` (command refresh)

Jalankan cepat:

```bash
php artisan test --filter='LayananFeatureTest|SmartTriageApiFeatureTest|RefreshSmartTriageCommandTest'
```

## Catatan Implementasi

- Smart Triage saat ini bersifat **deterministik** (tidak memakai model AI berbayar).
- Semua behavior bisa di-tuning dari `config/intelligence.php` tanpa ubah rumus inti.
- Jika beban data meningkat, optimasi pertama yang disarankan adalah memindahkan refresh massal ke queue/job per chunk.

