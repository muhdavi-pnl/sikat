<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 24px;
        }
        h1 {
            margin-bottom: 6px;
        }
        p {
            margin-top: 0;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            font-size: 12px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
        }
        .text-center {
            text-align: center;
        }
        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Dicetak pada {{ now()->format('d-m-Y H:i') }}</p>

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
                    <td>{{ $pegawai->nik ?: '-' }}</td>
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

    <script>
        window.print();
    </script>
</body>
</html>


