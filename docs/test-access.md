# Test / Demo Login Accounts

Sumber data akun seed: `database/seeders/UserSeeder.php`.

## Default Login

- Password default seluruh akun seed: **`Sikat2019`**
- Gunakan email sebagai username/login.

## Core Roles

| Role | Name | Email | Password | Notes |
|---|---|---|---|---|
| super-admin | Admin SIKAT | sikat@muhdavi.com | Sikat2019 | Full access |
| kepegawaian | Fakhruddin | fakhruddin@pnl.ac.id | Sikat2019 | Kepegawaian testing |
| jurusan | Salahuddin | salahuddintik@pnl.ac.id | Sikat2019 | Jurusan testing |

## Pegawai Accounts

Semua akun pada bagian ini memiliki:

- Role: `pegawai`
- Password: **`Sikat2019`**

Catatan:

- Daftar email pada bagian ini juga perlu tetap sinkron dengan pemetaan email di `database/seeders/PegawaiSeeder.php` (`pegawaiEmailsByName()`).
- Sinkronisasi ini dipakai agar akun login `pegawai` dapat otomatis terhubung ke record `pegawais` saat proses seed dijalankan.

| Name | Email |
|---|---|
| Afla Nevrisa | aflanevrisa@pnl.ac.id |
| Amirullah | amir@pnl.ac.id |
| Amri | amri@pnl.ac.id |
| Anwar | anwarsy@pnl.ac.id |
| Aswandi | aswandi@pnl.ac.id |
| Atthariq | atthariq.huzaifah@pnl.ac.id |
| Azhar | azhar.tik@pnl.ac.id |
| Fachri Yanuar Rudi F | fachri@pnl.ac.id |
| Guntur Syahputra | guntur@pnl.ac.id |
| Hari Toha Hidayat | haritoha@pnl.ac.id |
| Hendrawaty | hendrawaty@pnl.ac.id |
| Husaini | husaini@pnl.ac.id |
| Huzaeni | huzaeni@pnl.ac.id |
| Ilham Safar | ilham_safar@pnl.ac.id |
| Indrawati | indrawati@pnl.ac.id |
| Jamilah | jamilah@pnl.ac.id |
| M. Khadafi | mkhadafi@pnl.ac.id |
| Mahdi | mahdi@pnl.ac.id |
| Mahlil | mahlil@pnl.ac.id |
| Muhammad Arhami | muhammad.arhami@pnl.ac.id |
| Muhammad Azzahari | azzahari@pnl.ac.id |
| Muhammad Davi | muhammad.davi@pnl.ac.id |
| Muhammad Nasir | muhnasir.tmj@pnl.ac.id |
| Muhammad Reza Zulman | rezazulman@pnl.ac.id |
| Muhammad Rizka | rizka@pnl.ac.id |
| Mulyadi | mulyadi@pnl.ac.id |
| Mursyidah | mursyidah@pnl.ac.id |
| Mustainul Abdi | mustainul.abdi@pnl.ac.id |
| Nanda Saputri | nandasaputri@pnl.ac.id |
| Nanang Prihatin | nanang@pnl.ac.id |
| Novira Dwina | noviradwina@pnl.ac.id |
| Rahmad Hidayat | rahmad_hidayat@pnl.ac.id |
| Radhiyatammardhiyyah | radhiyah.td@pnl.ac.id |
| Rika Rahmawati | rikarahmawati@pnl.ac.id |
| Safriadi | safriadi@pnl.ac.id |
| Umri Erdiansyah | umri@pnl.ac.id |
| Zulfan Khairil Simbolon | zulfan@pnl.ac.id |

## Maintenance

- File ini ditujukan untuk testing / demo environment.
- `UserSeeder` adalah source of truth untuk daftar akun seed.
- Seluruh akun login `pegawai` yang di-seed sekarang sudah memiliki pasangan record di tabel `pegawais` melalui `PegawaiSeeder`.
- Jika `UserSeeder` berubah, file ini juga perlu diperbarui.
- Untuk memuat ulang akun seed:

```bash
php artisan db:seed --class=UserSeeder --force
```

