<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 12mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 0;
        }
        .page {
            max-width: 760px;
            margin: 0 auto;
        }
        .title {
            text-align: center;
            font-weight: 700;
            font-size: 16px;
            margin: 0 0 14px;
            text-transform: uppercase;
        }
        .section-title {
            font-weight: 700;
            text-transform: uppercase;
            background: #f3f4f6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid #111827;
            padding: 4px 6px;
            vertical-align: top;
        }
        .label {
            width: 120px;
            font-weight: 700;
            white-space: nowrap;
        }
        .narrow {
            width: 80px;
            white-space: nowrap;
            font-weight: 700;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        .signature-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
        }
        .signature-block {
            min-height: 112px;
        }
        .signed {
            display: inline-block;
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 3px 16px;
            border-radius: 6px;
            font-size: 10px;
            font-style: italic;
            font-weight: 700;
            margin: 12px 0 18px;
        }
        .mt-8 {
            margin-top: 8px;
        }
        .mt-12 {
            margin-top: 12px;
        }
        .note {
            margin-top: 12px;
            font-size: 10px;
        }
        .note ul {
            margin: 4px 0 0 16px;
            padding: 0;
        }
        .muted {
            color: #4b5563;
        }
        .no-border {
            border: 0;
        }
        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    @php
        $pegawai = $formData['pegawai'];
        $cuti = $formData['cuti'];
        $atasanLangsung = $formData['atasan_langsung'];
        $processorPegawai = $formData['processor_pegawai'];
        $catatanCuti = collect($formData['catatan_cuti'] ?? [])->sortBy('tahun')->values();
        $approval = $formData['approval'] ?? ['atasan' => null, 'pejabat' => null];
        $unitKerja = optional($pegawai->unit_kerja)->unit_kerja ?: '-';
        $programStudi = optional($pegawai->program_studi)->nama_prodi;
        $jabatanLabel = $pegawai->jabatan_fungsional
            ? \App\Models\Pegawai::jabatanFungsionalLabel($pegawai->jabatan_fungsional)
            : (optional($pegawai->jabatan)->jabatan ?: '-');
        $applicantName = strtoupper((string) ($pegawai->nama ?? '-'));
        $processorName = strtoupper((string) ($processorPegawai->nama ?? optional($formData['processor'])->name ?? '........................................'));
        $processorNip = (string) ($processorPegawai->nip ?? '');
        $processorJabatan = $processorPegawai
            ? ($processorPegawai->jabatan_fungsional
                ? \App\Models\Pegawai::jabatanFungsionalLabel($processorPegawai->jabatan_fungsional)
                : (optional($processorPegawai->jabatan)->jabatan ?: 'Petugas Kepegawaian'))
            : 'Petugas Kepegawaian';
        $mark = function (?string $selected, string $value): string {
            return $selected === $value ? '[v]' : '[ ]';
        };
        $approvalOptions = [
            'disetujui' => 'DISETUJUI',
            'perubahan' => 'PERUBAHAN',
            'ditangguhkan' => 'DITANGGUHKAN',
            'tidak_disetujui' => 'TIDAK DISETUJUI',
        ];
        $yearLabels = [];
        foreach ($catatanCuti as $index => $row) {
            $yearLabels[] = [
                'label' => $index === 0
                    ? 'N-' . max($catatanCuti->count() - 1, 0) . ' (' . $row['tahun'] . ')'
                    : ($index === ($catatanCuti->count() - 1)
                        ? 'N (' . $row['tahun'] . ')'
                        : 'N-' . ($catatanCuti->count() - $index - 1) . ' (' . $row['tahun'] . ')'),
                'row' => $row,
            ];
        }
    @endphp

    <div class="page">
        <div class="title">{{ strtoupper($title) }}</div>

        <table>
            <tr>
                <td colspan="4" class="section-title">I. DATA PEGAWAI</td>
            </tr>
            <tr>
                <td class="label">Nama</td>
                <td>{{ $applicantName }}</td>
                <td class="narrow">NIP</td>
                <td>{{ $pegawai->nip ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td>{{ $jabatanLabel }}</td>
                <td class="narrow">Masa Kerja</td>
                <td>{{ $formData['masa_kerja'] }}</td>
            </tr>
            <tr>
                <td class="label">Unit Kerja</td>
                <td colspan="3">
                    {{ $unitKerja }}
                    @if($programStudi)
                        / {{ $programStudi }}
                    @endif
                </td>
            </tr>
        </table>

        <table class="mt-8">
            <tr>
                <td colspan="2" class="section-title">II. JENIS CUTI YANG DIAMBIL</td>
            </tr>
            <tr>
                <td>{{ $mark($cuti->jenis_cuti ?? null, 'tahunan') }} 1. Cuti Tahunan</td>
                <td>{{ $mark($cuti->jenis_cuti ?? null, 'besar') }} 2. Cuti Besar</td>
            </tr>
            <tr>
                <td>{{ $mark($cuti->jenis_cuti ?? null, 'sakit') }} 3. Cuti Sakit</td>
                <td>{{ $mark($cuti->jenis_cuti ?? null, 'melahirkan') }} 4. Cuti Melahirkan</td>
            </tr>
            <tr>
                <td>{{ $mark($cuti->jenis_cuti ?? null, 'alasan_penting') }} 5. Cuti Karena Alasan Penting</td>
                <td>{{ $mark($cuti->jenis_cuti ?? null, 'di_luar_tanggungan_negara') }} 6. Cuti di Luar Tanggungan Negara</td>
            </tr>
        </table>

        <table class="mt-8">
            <tr>
                <td class="section-title">III. ALASAN CUTI</td>
            </tr>
            <tr>
                <td>{{ $cuti->alasan_cuti ?: '-' }}</td>
            </tr>
        </table>

        <table class="mt-8">
            <tr>
                <td colspan="6" class="section-title">IV. LAMANYA CUTI</td>
            </tr>
            <tr>
                <td class="label">SELAMA</td>
                <td>{{ (int) ($cuti->hari_diminta ?? 0) }} HARI</td>
                <td class="label">MULAI TANGGAL</td>
                <td>{{ optional($cuti->tanggal_mulai)->translatedFormat('d F Y') ?: '-' }}</td>
                <td class="label center">S.D</td>
                <td>{{ optional($cuti->tanggal_selesai)->translatedFormat('d F Y') ?: '-' }}</td>
            </tr>
        </table>

        <table class="mt-8">
            <tr>
                <td colspan="2" class="section-title">V. CATATAN CUTI</td>
            </tr>
            <tr>
                <td style="width: 58%; padding: 0;">
                    <table style="border: 0;">
                        <tr>
                            <td colspan="3">1. CUTI TAHUNAN</td>
                        </tr>
                        <tr>
                            <td class="center"><strong>TAHUN</strong></td>
                            <td class="center"><strong>SISA</strong></td>
                            <td class="center"><strong>KETERANGAN</strong></td>
                        </tr>
                        @foreach($yearLabels as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="center">{{ $row['row']['sisa'] ?? 0 }}</td>
                                <td>{{ ($row['row']['hari_diambil'] ?? 0) > 0 ? ('Dipakai ' . $row['row']['hari_diambil'] . ' hari') : '-' }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
                <td style="width: 42%; padding: 0;">
                    <table style="border: 0;">
                        <tr><td>2. CUTI BESAR</td></tr>
                        <tr><td>3. CUTI SAKIT</td></tr>
                        <tr><td>4. CUTI MELAHIRKAN</td></tr>
                        <tr><td>5. CUTI KARENA ALASAN PENTING</td></tr>
                        <tr><td>6. CUTI DI LUAR TANGGUNGAN NEGARA</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="mt-8">
            <tr>
                <td colspan="2" class="section-title">VI. ALAMAT SELAMA MENJALANKAN CUTI</td>
            </tr>
            <tr>
                <td style="width: 66%;">
                    <div>{{ $cuti->alamat_menjalankan_cuti ?: '-' }}</div>
                    <div class="mt-8"><strong>TELEPON:</strong> {{ $cuti->nomor_telepon_cuti ?: '-' }}</div>
                </td>
                <td style="width: 34%;" class="center signature-block">
                    <div>Hormat Saya,</div>
                    <div class="signed">Telah Ditandatangani</div>
                    <div><strong>{{ $applicantName }}</strong></div>
                    <div>NIP {{ $pegawai->nip ?: '-' }}</div>
                </td>
            </tr>
        </table>

        @php
            $pybmcPegawai = $formData['pybmc_pegawai'] ?? null;
            $pybmcName = strtoupper((string) ($pybmcPegawai->nama ?? $formData['processor_pegawai']->nama ?? optional($formData['processor'])->name ?? '........................................'));
            $pybmcNip = (string) ($pybmcPegawai->nip ?? $formData['processor_pegawai']->nip ?? '');
            $pybmcJabatan = $formData['pybmc_custom_jabatan'] ?: ($pybmcPegawai
                ? ($pybmcPegawai->jabatan_fungsional
                    ? \App\Models\Pegawai::jabatanFungsionalLabel($pybmcPegawai->jabatan_fungsional)
                    : (optional($pybmcPegawai->jabatan)->jabatan ?: 'Pejabat Yang Berwenang Memberikan Cuti'))
                : $processorJabatan);
            $atasanSigned = !empty($formData['atasan_approved_at']) || ($approval['atasan'] === 'disetujui');
            $pybmcSigned = !empty($formData['pybmc_approved_at']) || ($approval['pejabat'] === 'disetujui') || optional($formData['processor'])->exists;
        @endphp

        <table class="mt-8">
            <tr>
                <td colspan="4" class="section-title">VII. PERTIMBANGAN ATASAN LANGSUNG</td>
            </tr>
            <tr>
                @foreach($approvalOptions as $value => $label)
                    <td class="center"><strong>{{ $label }}</strong></td>
                @endforeach
            </tr>
            <tr>
                @foreach($approvalOptions as $value => $label)
                    <td class="center">{{ $mark($approval['atasan'] ?? null, $value) }}</td>
                @endforeach
            </tr>
        </table>
        @if(!empty($formData['catatan_atasan']))
            <div class="mt-8"><strong>Catatan Atasan:</strong> {{ $formData['catatan_atasan'] }}</div>
        @endif
        <div class="right mt-8">
            <div class="muted">{{ $atasanLangsung ? (optional($atasanLangsung->jabatan)->jabatan ?: 'Atasan Langsung') : 'Atasan Langsung' }}</div>
            @if($atasanSigned && $atasanLangsung)
                <div class="signed">Telah Ditandatangani</div>
            @else
                <div style="height: 36px;"></div>
            @endif
            <div><strong>{{ $atasanLangsung ? strtoupper($atasanLangsung->nama) : '........................................' }}</strong></div>
            <div>{{ $atasanLangsung && $atasanLangsung->nip ? ('NIP ' . $atasanLangsung->nip) : '' }}</div>
        </div>

        <table class="mt-12">
            <tr>
                <td colspan="4" class="section-title">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI</td>
            </tr>
            <tr>
                @foreach($approvalOptions as $value => $label)
                    <td class="center"><strong>{{ $label }}</strong></td>
                @endforeach
            </tr>
            <tr>
                @foreach($approvalOptions as $value => $label)
                    <td class="center">{{ $mark($approval['pejabat'] ?? null, $value) }}</td>
                @endforeach
            </tr>
        </table>
        @if(!empty($formData['catatan_pybmc']))
            <div class="mt-8"><strong>Catatan PYBMC:</strong> {{ $formData['catatan_pybmc'] }}</div>
        @endif
        <div class="right mt-8">
            <div class="muted">{{ $pybmcJabatan }}</div>
            @if($pybmcSigned)
                <div class="signed">Telah Ditandatangani</div>
            @else
                <div style="height: 36px;"></div>
            @endif
            <div><strong>{{ $pybmcName }}</strong></div>
            <div>{{ $pybmcNip !== '' ? 'NIP ' . $pybmcNip : '' }}</div>
        </div>


        <div class="note">
            <strong>Catatan:</strong>
            <ul>
                <li>Coret yang tidak perlu.</li>
                <li>Pilih salah satu jenis cuti dengan memberi tanda centang (v).</li>
                <li>Kolom catatan cuti tahunan mengikuti perhitungan saldo cuti pada sistem.</li>
                <li>Dokumen ini dihasilkan dari layanan cuti pada aplikasi SIKAT.</li>
            </ul>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
