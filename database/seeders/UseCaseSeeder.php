<?php
// database/seeders/UseCaseSeeder.php (FIXED to add data for Informasi Umum)

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NavMenu;
use App\Models\UseCase;
use App\Models\UatData;
use App\Models\ReportData;
use App\Models\DatabaseData;

class UseCaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // === Kategori Utama: ePesantren ===
        // Buat menu "Beranda ePesantren"
        $homeMenuEpesantren = NavMenu::firstOrCreate(
            ['menu_nama' => 'Dashboard', 'category' => 'epesantren', 'menu_child' => 0],
            [
                'menu_link' => 'epesantren/beranda-epesantren',
                'menu_icon' => 'fa-solid fa-home',
                'menu_order' => 0,
                'menu_status' => 1,
            ]
        );

        $daftarTabelEpesantren = NavMenu::create([
            'menu_nama' => 'Daftar Tabel',
            'category' => 'epesantren',
            'menu_child' => 0,
            'menu_link' => 'epesantren/tabels-epesantren',
            'menu_icon' => 'fa-solid fa-table',
            'menu_order' => 1,
            'menu_status' => 1,

        ]);

        // Buat UseCase "Informasi Umum" untuk "Beranda ePesantren"
        $infoUmumEpesantren = UseCase::firstOrCreate(
            ['menu_id' => $homeMenuEpesantren->menu_id, 'usecase_id' => 'UC-' . $homeMenuEpesantren->menu_id . '-001'],
            [
                'nama_proses' => 'Informasi Umum',
                'deskripsi_aksi' => '# Informasi Umum Kategori Beranda ePesantren' . "\n\nIni adalah informasi pengantar untuk kategori ini. Anda dapat menambahkan tindakan-tindakan lain (use cases) di sini.",
                'aktor' => 'Sistem',
                'tujuan' => 'Memberikan gambaran umum kategori.',
                'kondisi_awal' => 'Pengguna mengakses halaman beranda kategori.',
                'kondisi_akhir' => 'Informasi umum ditampilkan.',
                'aksi_reaksi' => 'Pengguna membaca konten.',
                'reaksi_sistem' => 'Sistem menyajikan informasi.',
            ]
        );
    }
}