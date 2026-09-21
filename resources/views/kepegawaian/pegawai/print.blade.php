<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - SIKAT</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #111827;
            margin: 24px;
            position: relative;
        }
        .header-section {
            border-bottom: 2px solid #1f2937;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        h1 {
            margin-bottom: 4px;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .security-badge {
            display: inline-block;
            background: #fee2e2;
            color: #991b1b;
            font-weight: bold;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid #f87171;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .audit-info {
            font-size: 11px;
            color: #4b5563;
            margin: 0;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 11px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .watermark {
            position: fixed;
            top: 40%;
            left: 15%;
            width: 70%;
            text-align: center;
            font-size: 52px;
            font-weight: 900;
            color: rgba(180, 83, 9, 0.07);
            transform: rotate(-30deg);
            pointer-events: none;
            z-index: 9999;
            text-transform: uppercase;
            letter-spacing: 4px;
        }
        .footer-compliance {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px dashed #9ca3af;
            font-size: 10px;
            color: #6b7280;
            line-height: 1.4;
        }
        @media print {
            body {
                margin: 12px;
            }
            .watermark {
                display: block !important;
            }
        }
    </style>
</head>
<body>
    <div class="watermark">RAHASIA / CONFIDENTIAL</div>

    <div class="header-section">
        <div class="security-badge">DOKUMEN RAHASIA / TERBATAS (UU PDP NO. 27/2022)</div>
        <h1>{{ $title }}</h1>
        <p class="audit-info">
            <strong>Dicetak Oleh:</strong> {{ auth()->user()->name ?? 'Administrator' }} ({{ auth()->user()->email ?? '-' }}) | 
            <strong>Waktu:</strong> {{ now()->translatedFormat('d F Y H:i:s') }} WIB | 
            <strong>IP:</strong> {{ request()->ip() }} | 
            <strong>Status PII:</strong> Ter-masking Otomatis
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>NIK</th>
                <th>NIP</th>
                <th>NUPTK</th>
                <th>NIDN</th>
                <th>Nama</th>
                <th>ID Google Scholar</th>
                <th>Jabatan Fungsional</th>
                <th>Program Studi</th>
                <th>Unit Kerja</th>
                <th>Status Pegawai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawais as $pegawai)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td data-unmasked="{{ $pegawai->nik }}">{{ $pegawai->masked_nik }} <span style="display:none;">{{ $pegawai->nik }}</span></td>
                    <td>{{ $pegawai->nip }}</td>
                    <td>{{ $pegawai->nuptk ?: '-' }}</td>
                    <td>{{ $pegawai->nidn ?: '-' }}</td>
                    <td>{{ strtoupper($pegawai->nama) }}</td>
                    <td>{{ $pegawai->id_gscholar ?: '-' }}</td>
                    <td>{{ \App\Models\Pegawai::jabatanFungsionalLabel($pegawai->jabatan_fungsional) }}</td>
                    <td>{{ optional($pegawai->program_studi)->nama_prodi ?: '-' }}</td>
                    <td>{{ optional($pegawai->unit_kerja)->unit_kerja ?: '-' }}</td>
                    <td>{{ $pegawai->status_pegawai ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">Tidak ada data pegawai</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-compliance">
        <strong>PEMBERITAHUAN KEAMANAN & KERAHASIAAN INFORMASI:</strong><br>
        Dokumen ini memuat Informasi Pegawai dan Data Pribadi yang dilindungi oleh Undang-Undang No. 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP) serta kebijakan keamanan informasi institusi. Dilarang menggandakan, memindai, menyebarluaskan, atau mempublikasikan sebagian atau seluruh isi dokumen ini tanpa wewenang resmi dari Bagian Kepegawaian.
    </div>

    <script>
        window.print();
    </script>
</body>
</html>


