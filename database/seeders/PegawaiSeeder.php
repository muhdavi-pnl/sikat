<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $usersByName = User::query()
            ->get(['id', 'name', 'email'])
            ->keyBy(function (User $user) {
                return trim((string) $user->name);
            });

        $emailsByName = $this->pegawaiEmailsByName();

        $records = [
            //            Jurusan Teknologi Informasi dan Komputer
            //            TRKJ
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '1310058901',
                'nip' => '198905102022031006',
                'jenis_kelamin' => 1,
                'tmt' => '2022-03-01',
                'program_studi_id' => 29,
                'nama' => 'Muhammad Davi',
                'nama_lengkap' => 'Muhammad Davi, S.Kom., M.Cs.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=6LxT534AAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0030126311',
                'nip' => '196312311993032004',
                'jenis_kelamin' => 0,
                'tmt' => '1993-03-01',
                'program_studi_id' => 29,
                'nama' => 'Jamilah',
                'gelar_depan' => 'Dra.',
                'nama_lengkap' => 'Dra. Jamilah, M.Pd.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=JLYIqsAAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0002027212',
                'nip' => '197202022000121001',
                'jenis_kelamin' => 1,
                'tmt' => '2000-12-01',
                'program_studi_id' => 29,
                'nama' => 'Amri',
                'nama_lengkap' => 'Amri, S.S.T., M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=GT5VwNoAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0024097203',
                'nip' => '197209242010121001',
                'jenis_kelamin' => 1,
                'tmt' => '2010-12-01',
                'program_studi_id' => 29,
                'nama' => 'Aswandi',
                'nama_lengkap' => 'Aswandi, S.Kom., M.Kom.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=dDo9mPIAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0031107303',
                'nip' => '197310312001121001',
                'jenis_kelamin' => 1,
                'tmt' => '2001-12-01',
                'program_studi_id' => 29,
                'nama' => 'Husaini',
                'nama_lengkap' => 'Husaini, S.Si., M.IT.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=OTs37rUAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0115087404',
                'nip' => '197408152001122001',
                'jenis_kelamin' => 0,
                'tmt' => '2001-12-01',
                'program_studi_id' => 29,
                'nama' => 'Indrawati',
                'nama_lengkap' => 'Indrawati, S.S.T., M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=KSpfHSsAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0025107501',
                'nip' => '197510252001121003',
                'jenis_kelamin' => 1,
                'tmt' => '2001-12-01',
                'program_studi_id' => 29,
                'nama' => 'Anwar',
                'nama_lengkap' => 'Anwar, S.Si., M.Cs.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=ol49whUAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0024077810',
                'nip' => '197807242001121001',
                'jenis_kelamin' => 1,
                'tmt' => '2001-12-01',
                'program_studi_id' => 29,
                'nama' => 'Atthariq',
                'nama_lengkap' => 'Atthariq, S.S.T., M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=W0lr0NMAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0714108502',
                'nip' => '198510142014041001',
                'jenis_kelamin' => 1,
                'tmt' => '2014-04-01',
                'program_studi_id' => 29,
                'nama' => 'Hari Toha Hidayat',
                'nama_lengkap' => 'Hari Toha Hidayat, S.Si., M.Cs.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=IduHst8AAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0113109203',
                'nip' => '199210132022031003',
                'jenis_kelamin' => 1,
                'tmt' => '2022-03-01',
                'program_studi_id' => 29,
                'nama' => 'Umri Erdiansyah',
                'nama_lengkap' => 'Umri Erdiansyah, S.Kom., M.Kom.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=7UduKZMAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '1317119201',
                'nip' => '199211172022032007',
                'jenis_kelamin' => 0,
                'tmt' => '2022-03-01',
                'program_studi_id' => 29,
                'nama' => 'Afla Nevrisa',
                'nama_lengkap' => 'Afla Nevrisa, S.Kom., M.Kom.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=gkzRslQAAAAJ&citpid=1',
            ],

            //            TRMM
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0005017304',
                'nip' => '197301051999032003',
                'jenis_kelamin' => 0,
                'tmt' => '1999-03-01',
                'program_studi_id' => 30,
                'nama' => 'Mursyidah',
                'nama_lengkap' => 'Mursyidah, M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=GXq16s4AAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0007077507',
                'nip' => '197507071999031002',
                'jenis_kelamin' => 1,
                'tmt' => '1999-03-01',
                'program_studi_id' => 30,
                'nama' => 'Muhammad Nasir',
                'nama_lengkap' => 'Muhammad Nasir, S.T., M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=XFiAL1gAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0003038702',
                'nip' => '198703032019031010',
                'jenis_kelamin' => 1,
                'tmt' => '2019-03-01',
                'program_studi_id' => 30,
                'nama' => 'Mahlil',
                'nama_lengkap' => 'Mahlil, S.Pd., M.A.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=J-TgI3AAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0127118701',
                'nip' => '198711272020121006',
                'jenis_kelamin' => 1,
                'tmt' => '2020-12-01',
                'program_studi_id' => 30,
                'nama' => 'Guntur Syahputra',
                'nama_lengkap' => 'Guntur Syahputra, S.Kom., M.Kom.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=JaHnZ7iPuzQC&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '1306018801',
                'nip' => '198801062018031001',
                'jenis_kelamin' => 1,
                'tmt' => '2018-03-01',
                'program_studi_id' => 30,
                'nama' => 'Fachri Yanuar Rudi F',
                'nama_lengkap' => 'Fachri Yanuar Rudi F, M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=zlZIk1MAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0015019005',
                'nip' => '199001152019031014',
                'jenis_kelamin' => 1,
                'tmt' => '2019-03-01',
                'program_studi_id' => 30,
                'nama' => 'Ilham Safar',
                'nama_lengkap' => 'Ilham Safar, S.S.T., M.Kom.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=HvbM1fMAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0023079005',
                'nip' => '199007232019031012',
                'jenis_kelamin' => 1,
                'tmt' => '2019-03-01',
                'program_studi_id' => 30,
                'nama' => 'Safriadi',
                'nama_lengkap' => 'Safriadi, S.T., M.Kom.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=H2AuXBcAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0010119005',
                'nip' => '199011102022032008',
                'jenis_kelamin' => 0,
                'tmt' => '2022-03-01',
                'program_studi_id' => 30,
                'nama' => 'Novira Dwina',
                'nama_lengkap' => 'Novira Dwina, S.S.T., M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=r4d_t1oAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0020119103',
                'nip' => '199111202022032010',
                'jenis_kelamin' => 0,
                'tmt' => '2022-03-01',
                'program_studi_id' => 30,
                'nama' => 'Nanda Saputri',
                'nama_lengkap' => 'Nanda Saputri, S.S.T., M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=-9oeZfkAAAAJ&citpid=1',
            ],

            //            TI
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0030086404',
                'nip' => '196408301990031005',
                'jenis_kelamin' => 1,
                'tmt' => '1990-03-01',
                'program_studi_id' => 28,
                'nama' => 'Azhar',
                'nama_lengkap' => 'Azhar, S.T., M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=mfymwj8AAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0026027006',
                'nip' => '197002261998022001',
                'jenis_kelamin' => 0,
                'tmt' => '1998-02-01',
                'program_studi_id' => 28,
                'nama' => 'Hendrawaty',
                'nama_lengkap' => 'Hendrawaty, S.T., M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=Sxcwt5MAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0001067002',
                'nip' => '197006011995011001',
                'jenis_kelamin' => 1,
                'tmt' => '1995-01-01',
                'program_studi_id' => 28,
                'nama' => 'Huzaeni',
                'nama_lengkap' => 'Huzaeni, S.S.T., M.IT.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=UjBo-2cAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0002087004',
                'nip' => '197008021993031001',
                'jenis_kelamin' => 1,
                'tmt' => '1993-03-01',
                'program_studi_id' => 28,
                'nama' => 'Mahdi',
                'nama_lengkap' => 'Mahdi, S.T., M.Cs.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=xUOyNp0AAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0002096906',
                'nip' => '196909021993031004',
                'jenis_kelamin' => 1,
                'tmt' => '1993-03-01',
                'program_studi_id' => 28,
                'nama' => 'Zulfan Khairil Simbolon',
                'nama_lengkap' => 'Zulfan Khairil Simbolon, S.S.T., M.Eng.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=SWVger8AAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0024047404',
                'nip' => '197404242002121001',
                'jenis_kelamin' => 1,
                'tmt' => '2002-12-01',
                'program_studi_id' => 28,
                'nama' => 'Salahuddin',
                'nama_lengkap' => 'Salahuddin, S.T., M.Cs.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=JDpt2RYAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0029107402',
                'nip' => '197410292000031001',
                'jenis_kelamin' => 1,
                'tmt' => '2000-03-01',
                'program_studi_id' => 28,
                'nama' => 'Muhammad Arhami',
                'nama_lengkap' => 'Muhammad Arhami, S.Si., M.Kom.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=ukPO3qkAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0018077503',
                'nip' => '197507182002121004',
                'jenis_kelamin' => 1,
                'tmt' => '2002-12-01',
                'program_studi_id' => 28,
                'nama' => 'M. Khadafi',
                'nama_lengkap' => 'M. Khadafi, S.T., M.T.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=L2oXRyQAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0120048303',
                'nip' => '198304202012121003',
                'jenis_kelamin' => 1,
                'tmt' => '2012-12-01',
                'program_studi_id' => 28,
                'nama' => 'Rahmad Hidayat',
                'gelar_depan' => 'Dr.',
                'nama_lengkap' => 'Dr. Rahmad Hidayat, S.Kom., M.Cs.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=Hin-dNEAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0009108802',
                'nip' => '198810092015041001',
                'jenis_kelamin' => 1,
                'tmt' => '2015-04-01',
                'program_studi_id' => 28,
                'nama' => 'Muhammad Rizka',
                'nama_lengkap' => 'Muhammad Rizka, S.S.T., M.Kom.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=vqH0bGYAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0028088906',
                'nip' => '198908282018031001',
                'jenis_kelamin' => 1,
                'tmt' => '2018-03-01',
                'program_studi_id' => 28,
                'nama' => 'Amirullah',
                'nama_lengkap' => 'Amirullah, S.S.T., M.Kom.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=aomXWPQAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0023077306',
                'nip' => '197307232002121001',
                'jenis_kelamin' => 1,
                'tmt' => '2002-12-01',
                'program_studi_id' => 28,
                'nama' => 'Mulyadi',
                'nama_lengkap' => 'Mulyadi, S.T., M.Eng.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=VEJAd9MAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0730109102',
                'nip' => '199110302019031015',
                'jenis_kelamin' => 1,
                'tmt' => '2019-03-01',
                'program_studi_id' => 28,
                'nama' => 'Mustainul Abdi',
                'nama_lengkap' => 'Mustainul Abdi, S.S.T., M.Kom.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=JA3V8u0AAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0001059205',
                'nip' => '199205012022031005',
                'jenis_kelamin' => 1,
                'tmt' => '2022-03-01',
                'program_studi_id' => 28,
                'nama' => 'Muhammad Reza Zulman',
                'nama_lengkap' => 'Muhammad Reza Zulman, S.S.T., M.Sc.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=O_ZLx-cAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0026089202',
                'nip' => '199208262022032011',
                'jenis_kelamin' => 0,
                'tmt' => '2022-03-01',
                'program_studi_id' => 28,
                'nama' => 'Radhiyatammardhiyyah',
                'nama_lengkap' => 'Radhiyatammardhiyyah, S.S.T., M.Sc.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=2FIMPSMAAAAJ&citpid=1',
            ],

            //            Jurusan Bisnis
            [
                'created_at' => date('Y-m-d H:i:s'),
                'agama_id' => 1,
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'program_studi_id' => 26,
                'nip' => '198906052022031006',
                'nama' => 'Lakharis Inuzula',
            ],
            [
                'created_at' => date('Y-m-d H:i:s'),
                'agama_id' => 1,
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'program_studi_id' => 26,
                'nip' => '199101212022032008',
                'nama' => 'Julia Alfianti',
            ],

            //            Jurusan Teknik Elektro
            [
                'created_at' => date('Y-m-d H:i:s'),
                'agama_id' => 1,
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'program_studi_id' => 17,
                'nip' => '197911122003121003',
                'nama' => 'Zamzami',
            ],

            //            Jurusan Teknik Kimia
            [
                'created_at' => date('Y-m-d H:i:s'),
                'agama_id' => 1,
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'program_studi_id' => 13,
                'nip' => '197703062002122003',
                'nama' => 'Faridah',
            ],
            [
                'created_at' => date('Y-m-d H:i:s'),
                'agama_id' => 1,
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'program_studi_id' => 11,
                'nip' => '196907101997021001',
                'nama' => 'Teuku Rihayat',
            ],

            //            Jurusan Teknik Mesin
            [
                'created_at' => date('Y-m-d H:i:s'),
                'agama_id' => 1,
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'program_studi_id' => 8,
                'nip' => '197212021999031001',
                'nama' => 'Indra Mawardi',
            ],

            //            Jurusan Teknik Sipil
            [
                'created_at' => date('Y-m-d H:i:s'),
                'agama_id' => 1,
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'program_studi_id' => 4,
                'nip' => '197812162002121003',
                'nama' => 'Rizal Syahyadi',
            ],
            [
                'created_at' => date('Y-m-d H:i:s'),
                'agama_id' => 1,
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'program_studi_id' => 4,
                'nip' => '198812082022032004',
                'nama' => 'Tursina',
            ],
            [
                'created_at' => date('Y-m-d H:i:s'),
                'agama_id' => 1,
                'gelar_depan' => null,
                'gelar_belakang' => null,
                'program_studi_id' => 4,
                'nip' => '199105052022031007',
                'nama' => 'Deni Iqbal',
            ],

            //            Jurusan TIK Tambahan
            [
                'created_at' => now(),
                'updated_at' => now(),
                'agama_id' => 1,
                'nidn' => '0019057205',
                'nip' => '197205191999031002',
                'jenis_kelamin' => 1,
                'tmt' => '1999-03-01',
                'program_studi_id' => 29,
                'nama' => 'Nanang Prihatin',
                'nama_lengkap' => 'Nanang Prihatin, S.Kom., M.Cs.',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=IKZ8g48AAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nip' => '199001010000000100',
                'nama' => 'Nurul Hidayati Binti Saidan',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nip' => '199001010000000101',
                'nama' => 'M. Arif Nugraha',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nip' => '199001010000000102',
                'nama' => 'Cut Dwita Rahma',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nip' => '199001010000000103',
                'nama' => 'Supriadi',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nip' => '199001010000000104',
                'nama' => 'T. Dany Dhaifullah',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nip' => '199001010000000105',
                'nama' => 'Zetta Fazira',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nip' => '199001010000000106',
                'nama' => 'Nazira Suha Al Bakri',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nip' => '199001010000000107',
                'nama' => 'Mahmud',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nip' => '199001010000000108',
                'nama' => 'Azhara Ramadhanti Widodo',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nip' => '199001010000000109',
                'nama' => 'Ahmadi',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => '0010069307',
                'nip' => '199206102024211001',
                'kelompok_bidang_keahlian_id' => 2,
                'jenis_kelamin' => 1,
                'tmt' => '2024-01-01',
                'program_studi_id' => 29,
                'nama_lengkap' => 'Muhammad Azzahari, S.S.T., M.T.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=jdsb3LYAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => '0022029211',
                'nip' => '199202222024062002',
                'kelompok_bidang_keahlian_id' => 3,
                'jenis_kelamin' => 0,
                'tmt' => '2024-06-01',
                'program_studi_id' => 29,
                'nama_lengkap' => 'Rika Rahmawati, M.Kom.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=gkzRslQAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => '1309098903',
                'nip' => '198909092025061001',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 4,
                'jenis_kelamin' => 1,
                'nama_lengkap' => 'Riwanul Nasron, S.T., M.T.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=HUSepDgAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => '1315029001',
                'nip' => '199002152025062005',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 5,
                'jenis_kelamin' => 0,
                'nama_lengkap' => 'Husna Gemasih, S.Inf., M.Cs.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=RXIEF7FOyBUC&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => null,
                'nip' => '199006052025061005',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 6,
                'jenis_kelamin' => 1,
                'nama_lengkap' => 'Firdaus Muttaqin, S.T., M.T.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=HUSepDgAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => '0128079302',
                'nip' => '199307282025062003',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 7,
                'jenis_kelamin' => 0,
                'nama_lengkap' => 'Mutiara S. Simanjuntak, S.Kom., M.Kom.',
                'bidang_keilmuan' => 'Image Processing, Intellegen Artificial, Computer Vision',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=el4tFbEAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => '0117119301',
                'nip' => '199311172025062004',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 8,
                'jenis_kelamin' => 1,
                'nama_lengkap' => 'Erika Fahmi Br Ginting, S.Kom., M.Kom.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=HUSepDgAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => null,
                'nip' => '199402282025061002',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 9,
                'jenis_kelamin' => 1,
                'nama_lengkap' => 'Muhammad Hari Hasibuan, M.Kom.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=fx3WGF0AAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => '0125049401',
                'nip' => '199404252025062005',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 10,
                'jenis_kelamin' => 0,
                'nama_lengkap' => 'Suci Andriyani, M.Kom.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=c9R9_E0AAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => '7700008435',
                'nip' => '199405252025061009',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 11,
                'jenis_kelamin' => 1,
                'nama_lengkap' => 'Arwin Putra, M.Kom.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=HUSepDgAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => null,
                'nip' => '199504032025062004',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 12,
                'jenis_kelamin' => 1,
                'nama_lengkap' => 'Rizqina Barophon, S.Pd., M.Pd.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=HUSepDgAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => null,
                'nip' => '199601112025061002',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 1,
                'jenis_kelamin' => 1,
                'nama_lengkap' => 'Ahmad Afif, M.Kom.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=HUSepDgAAAAJ&citpid=1',
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
                'nidn' => null,
                'nip' => '199611302025061006',
                'tmt' => '2025-06-01',
                'program_studi_id' => 29,
                'kelompok_bidang_keahlian_id' => 12,
                'jenis_kelamin' => 1,
                'nama_lengkap' => 'Hosea Sitepu, M.Pd.',
                'bidang_keilmuan' => 'jaringan',
                'foto' => 'https://scholar.google.co.id/citations?view_op=view_photo&user=aQUxmXsAAAAJ&citpid=1',
            ],
        ];

        $identifierFields = ['id_wos', 'id_orc', 'id_sinta', 'id_scopus', 'id_garuda', 'id_gscholar', 'nidn', 'nuptk'];

        // First, extract identifier fields from raw records before normalization
        $rawIdentitasRecords = [];
        foreach ($records as $key => &$record) {
            $identitas = [];
            foreach ($identifierFields as $field) {
                if (isset($record[$field]) && !empty($record[$field])) {
                    $identitas[$field] = $record[$field];
                }
            }
            // Store by NIP for later lookup
            if ($identitas !== []) {
                $rawIdentitasRecords[$record['nip']] = $identitas;
            }
        }
        unset($record);

        $records = collect($records)
            ->map(function (array $record) use ($usersByName, $emailsByName) {
                return $this->normalizePegawaiRecord($record, $usersByName, $emailsByName);
            })
            ->pipe(function ($records) {
                return $this->nullDuplicateUniqueValues($records, ['nik']);
            })
            ->all();

        DB::table('pegawais')->upsert(
            $records,
            ['nip'],
            [
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
                'tmt_cpns',
                'tmt_pns',
                'tmt_jabatan',
                'email',
                'no_hp',
                'no_telp',
                'alamat',
                'kelurahan_id',
                'eselon_id',
                'kedudukan_pegawai_id',
                'agama_id',
                'jabatan_id',
                'pangkat_id',
                'status_perkawinan_id',
                'pendidikan_id',
                'program_studi_id',
                'unit_kerja_id',
                'user_id',
                'status_pegawai',
                'cuti_hari_tersedia',
                'updated_at',
            ]
        );

        // Now insert identifier records with correct pegawai_id
        foreach ($rawIdentitasRecords as $nip => $identitas) {
            $pegawaiId = DB::table('pegawais')->where('nip', $nip)->value('id');
            if ($pegawaiId) {
                $identitas['pegawai_id'] = $pegawaiId;
                $identitas['created_at'] = now();
                $identitas['updated_at'] = now();
                DB::table('pegawai_identitas')->updateOrInsert(
                    ['pegawai_id' => $pegawaiId],
                    $identitas
                );
            }
        }
    }

    protected function normalizePegawaiRecord(array $record, $usersByName, array $emailsByName): array
    {
        [$nama, $gelarBelakang] = $this->splitNamaLengkap((string) ($record['nama_lengkap'] ?? ''));

        if (!isset($record['nama']) && $nama !== null) {
            $record['nama'] = $nama;
        }

        if (!isset($record['gelar_belakang']) && $gelarBelakang !== null) {
            $record['gelar_belakang'] = $gelarBelakang;
        }

        if (!isset($record['tmt_jabatan']) && !empty($record['tmt'])) {
            $record['tmt_jabatan'] = $record['tmt'];
        }

        if (!isset($record['id_gscholar']) && !empty($record['foto'])) {
            $record['id_gscholar'] = $this->extractGoogleScholarId((string) $record['foto']);
        }

        $namaUser = trim((string) ($record['nama'] ?? ''));
        $matchedUser = $namaUser !== '' ? $usersByName->get($namaUser) : null;

        if (!isset($record['email']) && $namaUser !== '' && isset($emailsByName[$namaUser])) {
            $record['email'] = $emailsByName[$namaUser];
        }

        if (!isset($record['user_id']) && $matchedUser) {
            $record['user_id'] = $matchedUser->id;
        }

        if (!isset($record['email']) && $matchedUser) {
            $record['email'] = $matchedUser->email;
        }

        return array_merge([
            'nip' => null,
            'nik' => null,
            'nama' => null,
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'tempat_lahir' => null,
            'tanggal_lahir' => null,
            'jenis_kelamin' => null,
            'jumlah_anak' => null,
            'tanggal_lulus' => null,
            'npwp' => null,
            'bpjs' => null,
            'no_karpeg' => null,
            'no_karis_karsu' => null,
            'tmt_cpns' => null,
            'tmt_pns' => null,
            'tmt_jabatan' => null,
            'email' => null,
            'no_hp' => null,
            'no_telp' => null,
            'alamat' => null,
            'kelurahan_id' => null,
            'eselon_id' => null,
            'kedudukan_pegawai_id' => null,
            'agama_id' => null,
            'jabatan_id' => null,
            'pangkat_id' => null,
            'status_perkawinan_id' => null,
            'pendidikan_id' => null,
            'program_studi_id' => null,
            'unit_kerja_id' => null,
            'user_id' => null,
            'status_pegawai' => 'PNS',
            'cuti_hari_tersedia' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ], array_intersect_key($record, array_flip([
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
            'tmt_cpns',
            'tmt_pns',
            'tmt_jabatan',
            'email',
            'no_hp',
            'no_telp',
            'alamat',
            'kelurahan_id',
            'eselon_id',
            'kedudukan_pegawai_id',
            'agama_id',
            'jabatan_id',
            'pangkat_id',
            'status_perkawinan_id',
            'pendidikan_id',
            'program_studi_id',
            'unit_kerja_id',
            'user_id',
            'status_pegawai',
            'cuti_hari_tersedia',
            'created_at',
            'updated_at',
        ])));
    }

    protected function splitNamaLengkap(string $namaLengkap): array
    {
        $namaLengkap = trim($namaLengkap);

        if ($namaLengkap === '') {
            return [null, null];
        }

        $parts = array_map('trim', explode(',', $namaLengkap, 2));

        return [
            $parts[0] !== '' ? $parts[0] : null,
            isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null,
        ];
    }

    protected function extractGoogleScholarId(string $url): ?string
    {
        $query = parse_url($url, PHP_URL_QUERY);

        if (!is_string($query) || $query === '') {
            return null;
        }

        parse_str($query, $params);

        $user = trim((string) ($params['user'] ?? ''));

        return $user !== '' ? $user : null;
    }

    protected function normalizeJabatanFungsionalValue($value): ?string
    {
        $normalized = trim(mb_strtolower((string) ($value ?? '')));

        if ($normalized === '' || $normalized === 'tenaga pengajar') {
            return null;
        }

        return $normalized;
    }

    protected function nullDuplicateUniqueValues($records, array $columns)
    {
        foreach ($columns as $column) {
            $duplicateValues = collect($records)
                ->map(function (array $record) use ($column) {
                    return trim((string) ($record[$column] ?? ''));
                })
                ->filter()
                ->countBy()
                ->filter(function (int $count) {
                    return $count > 1;
                })
                ->keys()
                ->all();

            if ($duplicateValues === []) {
                continue;
            }

            $records = collect($records)
                ->map(function (array $record) use ($column, $duplicateValues) {
                    $value = trim((string) ($record[$column] ?? ''));

                    if ($value !== '' && in_array($value, $duplicateValues, true)) {
                        $record[$column] = null;
                    }

                    return $record;
                });
        }

        return $records;
    }

    protected function pegawaiEmailsByName(): array
    {
        return [
            'Jamilah' => 'jamilah@pnl.ac.id',
            'Azhar' => 'azhar.tik@pnl.ac.id',
            'Zulfan Khairil Simbolon' => 'zulfan@pnl.ac.id',
            'Hendrawaty' => 'hendrawaty@pnl.ac.id',
            'Huzaeni' => 'huzaeni@pnl.ac.id',
            'Mahdi' => 'mahdi@pnl.ac.id',
            'Amri' => 'amri@pnl.ac.id',
            'Nanang Prihatin' => 'nanang@pnl.ac.id',
            'Aswandi' => 'aswandi@pnl.ac.id',
            'Mursyidah' => 'mursyidah@pnl.ac.id',
            'Mulyadi' => 'mulyadi@pnl.ac.id',
            'Husaini' => 'husaini@pnl.ac.id',
            'Salahuddin' => 'salahuddintik@pnl.ac.id',
            'Indrawati' => 'indrawati@pnl.ac.id',
            'Muhammad Arhami' => 'muhammad.arhami@pnl.ac.id',
            'Muhammad Nasir' => 'muhnasir.tmj@pnl.ac.id',
            'M. Khadafi' => 'mkhadafi@pnl.ac.id',
            'Anwar' => 'anwarsy@pnl.ac.id',
            'Atthariq' => 'atthariq.huzaifah@pnl.ac.id',
            'Rahmad Hidayat' => 'rahmad_hidayat@pnl.ac.id',
            'Hari Toha Hidayat' => 'haritoha@pnl.ac.id',
            'Mahlil' => 'mahlil@pnl.ac.id',
            'Guntur Syahputra' => 'guntur@pnl.ac.id',
            'Fachri Yanuar Rudi F' => 'fachri@pnl.ac.id',
            'Muhammad Rizka' => 'rizka@pnl.ac.id',
            'Muhammad Davi' => 'muhammad.davi@pnl.ac.id',
            'Amirullah' => 'amir@pnl.ac.id',
            'Ilham Safar' => 'ilham_safar@pnl.ac.id',
            'Safriadi' => 'safriadi@pnl.ac.id',
            'Novira Dwina' => 'noviradwina@pnl.ac.id',
            'Mustainul Abdi' => 'mustainul.abdi@pnl.ac.id',
            'Nanda Saputri' => 'nandasaputri@pnl.ac.id',
            'Nurul Hidayati Binti Saidan' => 'nurulhidayatibs@pnl.ac.id',
            'Arwin Putra' => 'arwinptr@pnl.ac.id',
            'M. Arif Nugraha' => 'nugraha.arif@pnl.ac.id',
            'Ahmad Afif' => 'ahmadafif@pnl.ac.id',
            'Cut Dwita Rahma' => 'cutdwita@pnl.ac.id',
            'Supriadi' => 'supriadi@pnl.ac.id',
            'Muhammad Hari Hasibuan' => 'harihasibuan@pnl.ac.id',
            'Rizqina Barophon' => 'rizqinabarophon@pnl.ac.id',
            'Riwanul Nasron' => 'riwanulnasron@pnl.ac.id',
            'T. Dany Dhaifullah' => 'teukudany@pnl.ac.id',
            'Hosea Sitepu' => 'hoseasitepu@pnl.ac.id',
            'Zetta Fazira' => 'zettafazira@pnl.ac.id',
            'Nazira Suha Al Bakri' => 'nazirasuha@pnl.ac.id',
            'Mahmud' => 'mahmud@pnl.ac.id',
            'Firdaus Muttaqin' => 'firdausmuttaqin@pnl.ac.id',
            'Azhara Ramadhanti Widodo' => 'azhararamadhanti@pnl.ac.id',
            'Ahmadi' => 'ahmadi@pnl.ac.id',
            'Mutiara S. Simanjuntak' => 'mutiara@pnl.ac.id',
            'Erika Fahmi Br Ginting' => 'erikafahmi@pnl.ac.id',
            'Husna Gemasih' => 'husnagemasih@pnl.ac.id',
            'Suci Andriyani' => 'suciandriyani@pnl.ac.id',
            'Rika Rahmawati' => 'rikarahmawati@pnl.ac.id',
            'Muhammad Reza Zulman' => 'rezazulman@pnl.ac.id',
            'Muhammad Azzahari' => 'azzahari@pnl.ac.id',
            'Radhiyatammardhiyyah' => 'radhiyah.td@pnl.ac.id',
            'Umri Erdiansyah' => 'umri@pnl.ac.id',
            'Afla Nevrisa' => 'aflanevrisa@pnl.ac.id',
        ];
    }
}
