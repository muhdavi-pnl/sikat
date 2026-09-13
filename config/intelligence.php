<?php

return [
    // If empty, scoring uses all master dokumen in database.
    // If filled, only matching kode_dokumen will be scored.
    'required_document_codes' => [
    ],

    'profile_fields' => [
        'nip' => 'NIP',
        'nama' => 'Nama',
        'id_gscholar' => 'ID Google Scholar',
        'nik' => 'NIK',
        'email' => 'Email',
        'no_hp' => 'No. HP',
        'alamat' => 'Alamat Domisili',
        'unit_kerja_id' => 'Unit Kerja',
        'program_studi_id' => 'Program Studi',
        'jabatan_fungsional' => 'Jabatan',
    ],

    'layanan_profile_requirement_codes' => [
        'NIP' => 'nip',
        'NAMA' => 'nama',
        'ID_GSCHOLAR' => 'id_gscholar',
        'GOOGLE_SCHOLAR' => 'id_gscholar',
        'NIK' => 'nik',
        'EMAIL' => 'email',
        'NO_HP' => 'no_hp',
        'NO_TELP' => 'no_telp',
        'ALAMAT' => 'alamat',
        'ALAMAT_DOMISILI' => 'alamat',
        'UNIT_KERJA' => 'unit_kerja_id',
        'PROGRAM_STUDI' => 'program_studi_id',
        'JABATAN_FUNG' => 'jabatan_fungsional',
    ],

    'cuti_optional_requirement_codes' => [
        'CUTI_ATSN',
        'CUTI_BUKTI',
    ],

    'weights' => [
        'profile' => 30,
        'documents' => 70,
    ],

    'triage' => [
        'status_base_scores' => [
            'usulan' => 70,
            'pending' => 60,
            'proses' => 50,
            'selesai' => 5,
            'ditolak' => 0,
        ],

        'age' => [
            'hours_per_point' => 6,
            'max_bonus' => 20,
        ],

        'sla_hours' => [
            'default' => 48,
            'usulan' => 24,
            'pending' => 24,
            'proses' => 72,
        ],

        'risk_thresholds' => [
            'high_minutes' => 360,
            'medium_minutes' => 1440,
        ],

        'urgent_keywords' => [
            'mendesak',
            'urgent',
            'segera',
        ],

        'keyword_bonus' => 10,

        'terminal_statuses' => [
            'selesai',
            'ditolak',
        ],
    ],
];

