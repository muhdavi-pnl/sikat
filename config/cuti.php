<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tanggal Libur / Tidak Dihitung Sebagai Cuti
    |--------------------------------------------------------------------------
    |
    | Isi dengan salah satu format berikut:
    | - m-d     => libur berulang setiap tahun (contoh: 01-01)
    | - Y-m-d   => libur spesifik untuk tahun tertentu
    |
    | Tanggal-tanggal ini akan dikecualikan dari perhitungan hari cuti,
    | selain akhir pekan.
    |
    */

    'excluded_dates' => [
        // '01-01',
        // '12-25',
        // '2026-03-23',
        '01-01',        // Tahun Baru Masehi
        '2026-01-16',   // Isra Mi’raj Nabi Muhammad S.A.W.
        '2026-02-16',   // Cuti Bersama Tahun Baru Imlek
        '2026-02-17',   // Tahun Baru Imlek 2577 Kongzili
        '2026-02-18',   // Cuti Bersama Hari Suci Nyepi
        '2026-03-19',   // Hari Suci Nyepi (Tahun Baru Saka 1948)
        '2026-04-20',   // Cuti Bersama Idul Fitri 1447 H
        '2026-03-21',   // Idul Fitri 1447 H
        '2026-03-22',   // Idul Fitri 1447 H
        '2026-03-23',   // Cuti Bersama Idul Fitri
        '2026-03-24',   // Cuti Bersama Idul Fitri
        '2026-04-03',   // Wafat Yesus Kristus
        '2026-04-05',   // Kebangkitan Yesus Kristus (Paskah)
        '05-01',        // Hari Buruh Internasional
        '2026-05-14',   // Kenaikan Yesus Kristus
        '2026-05-15',   // Kenaikan Yesus Kristus
        '2026-05-27',   // Idul Adha 1447 H
        '2026-05-28',   // Cuti Bersama Idul Adha 1447 H
        '2026-05-31',   // Hari Raya Waisak 2570 BE
        '06-01',        // Hari Lahir Pancasila
        '2026-06-16',   // Tahun Baru Islam 1448 H (1 Muharam)
        '08-17',        // Hari Proklamasi Kemerdekaan
        '2026-08-25',   // Maulid Nabi Muhammad S.A.W.
        '2026-12-24',   // Cuti Bersama Kelahiran Yesus Kristus
        '2026-12-25',   // Kelahiran Yesus Kristus (Natal)
    ],
];

