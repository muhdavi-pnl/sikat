<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultPassword = Hash::make('Sikat2019');

        User::where('email', 'apt@muhdavi.com')->delete();

        $admin = User::updateOrCreate(
            ['email' => 'sikat@muhdavi.com'],
            [
                'name' => 'Muhammad D. Kahfi',
                'password' => $defaultPassword,
            ]
        );
        $admin->syncRoles(['super-admin']);
        $admin->syncPermissions(Permission::all());

        $kepegawaian = User::updateOrCreate(
            ['email' => 'fakhruddin@pnl.ac.id'],
            [
                'name' => 'Fakhruddin',
                'password' => $defaultPassword,
            ]
        );
        $kepegawaian->syncRoles(['kepegawaian']);

        $atasan = User::updateOrCreate(
            ['email' => 'salahuddintik@pnl.ac.id'],
            [
                'name' => 'Salahuddin',
                'password' => $defaultPassword,
            ]
        );
        $atasan->syncRoles(['atasan']);

        $pegawaiUsers = [
            ['name' => 'Jamilah', 'email' => 'jamilah@pnl.ac.id'],
            ['name' => 'Azhar', 'email' => 'azhar.tik@pnl.ac.id'],
            ['name' => 'Zulfan Khairil Simbolon', 'email' => 'zulfan@pnl.ac.id'],
            ['name' => 'Hendrawaty', 'email' => 'hendrawaty@pnl.ac.id'],
            ['name' => 'Huzaeni', 'email' => 'huzaeni@pnl.ac.id'],
            ['name' => 'Mahdi', 'email' => 'mahdi@pnl.ac.id'],
            ['name' => 'Amri', 'email' => 'amri@pnl.ac.id'],
            ['name' => 'Nanang Prihatin', 'email' => 'nanang@pnl.ac.id'],
            ['name' => 'Aswandi', 'email' => 'aswandi@pnl.ac.id'],
            ['name' => 'Mursyidah', 'email' => 'mursyidah@pnl.ac.id'],
            ['name' => 'Mulyadi', 'email' => 'mulyadi@pnl.ac.id'],
            ['name' => 'Husaini', 'email' => 'husaini@pnl.ac.id'],
            ['name' => 'Indrawati', 'email' => 'indrawati@pnl.ac.id'],
            ['name' => 'Muhammad Arhami', 'email' => 'muhammad.arhami@pnl.ac.id'],
            ['name' => 'Muhammad Nasir', 'email' => 'muhnasir.tmj@pnl.ac.id'],
            ['name' => 'M. Khadafi', 'email' => 'mkhadafi@pnl.ac.id'],
            ['name' => 'Anwar', 'email' => 'anwarsy@pnl.ac.id'],
            ['name' => 'Atthariq', 'email' => 'atthariq.huzaifah@pnl.ac.id'],
            ['name' => 'Rahmad Hidayat', 'email' => 'rahmad_hidayat@pnl.ac.id'],
            ['name' => 'Hari Toha Hidayat', 'email' => 'haritoha@pnl.ac.id'],
            ['name' => 'Mahlil', 'email' => 'mahlil@pnl.ac.id'],
            ['name' => 'Guntur Syahputra', 'email' => 'guntur@pnl.ac.id'],
            ['name' => 'Fachri Yanuar Rudi F', 'email' => 'fachri@pnl.ac.id'],
            ['name' => 'Muhammad Rizka', 'email' => 'rizka@pnl.ac.id'],
            ['name' => 'Muhammad Davi', 'email' => 'muhammad.davi@pnl.ac.id'],
            ['name' => 'Amirullah', 'email' => 'amir@pnl.ac.id'],
            ['name' => 'Ilham Safar', 'email' => 'ilham_safar@pnl.ac.id'],
            ['name' => 'Safriadi', 'email' => 'safriadi@pnl.ac.id'],
            ['name' => 'Novira Dwina', 'email' => 'noviradwina@pnl.ac.id'],
            ['name' => 'Mustainul Abdi', 'email' => 'mustainul.abdi@pnl.ac.id'],
            ['name' => 'Nanda Saputri', 'email' => 'nandasaputri@pnl.ac.id'],
            ['name' => 'Rika Rahmawati', 'email' => 'rikarahmawati@pnl.ac.id'],
            ['name' => 'Muhammad Reza Zulman', 'email' => 'rezazulman@pnl.ac.id'],
            ['name' => 'Muhammad Azzahari', 'email' => 'azzahari@pnl.ac.id'],
            ['name' => 'Radhiyatammardhiyyah', 'email' => 'radhiyah.td@pnl.ac.id'],
            ['name' => 'Umri Erdiansyah', 'email' => 'umri@pnl.ac.id'],
            ['name' => 'Afla Nevrisa', 'email' => 'aflanevrisa@pnl.ac.id'],
            ['name' => 'Nurul Hidayati Binti Saidan', 'email' => 'nurulhidayatibs@pnl.ac.id'],
            ['name' => 'Arwin Putra', 'email' => 'arwinptr@pnl.ac.id'],
            ['name' => 'M. Arif Nugraha', 'email' => 'nugraha.arif@pnl.ac.id'],
            ['name' => 'Ahmad Afif', 'email' => 'ahmadafif@pnl.ac.id'],
            ['name' => 'Cut Dwita Rahma', 'email' => 'cutdwita@pnl.ac.id'],
            ['name' => 'Supriadi', 'email' => 'supriadi@pnl.ac.id'],
            ['name' => 'Muhammad Hari Hasibuan', 'email' => 'harihasibuan@pnl.ac.id'],
            ['name' => 'Rizqina Barophon', 'email' => 'rizqinabarophon@pnl.ac.id'],
            ['name' => 'Riwanul Nasron', 'email' => 'riwanulnasron@pnl.ac.id'],
            ['name' => 'T. Dany Dhaifullah', 'email' => 'teukudany@pnl.ac.id'],
            ['name' => 'Hosea Sitepu', 'email' => 'hoseasitepu@pnl.ac.id'],
            ['name' => 'Zetta Fazira', 'email' => 'zettafazira@pnl.ac.id'],
            ['name' => 'Nazira Suha Al Bakri', 'email' => 'nazirasuha@pnl.ac.id'],
            ['name' => 'Mahmud', 'email' => 'mahmud@pnl.ac.id'],
            ['name' => 'Firdaus Muttaqin', 'email' => 'firdausmuttaqin@pnl.ac.id'],
            ['name' => 'Azhara Ramadhanti Widodo', 'email' => 'azhararamadhanti@pnl.ac.id'],
            ['name' => 'Ahmadi', 'email' => 'ahmadi@pnl.ac.id'],
            ['name' => 'Mutiara S. Simanjuntak', 'email' => 'mutiara@pnl.ac.id'],
            ['name' => 'Erika Fahmi Br Ginting', 'email' => 'erikafahmi@pnl.ac.id'],
            ['name' => 'Husna Gemasih', 'email' => 'husnagemasih@pnl.ac.id'],
            ['name' => 'Suci Andriyani', 'email' => 'suciandriyani@pnl.ac.id'],
        ];

        foreach ($pegawaiUsers as $pegawaiUser) {
            $user = User::updateOrCreate(
                ['email' => $pegawaiUser['email']],
                [
                    'name' => $pegawaiUser['name'],
                    'password' => $defaultPassword,
                ]
            );

            $user->syncRoles(['pegawai']);
        }
    }
}
