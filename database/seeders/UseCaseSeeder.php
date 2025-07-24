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
            ['menu_nama' => 'Beranda ePesantren', 'category' => 'epesantren', 'menu_child' => 0],
            [
                'menu_link' => 'epesantren/beranda-epesantren',
                'menu_icon' => 'fa-solid fa-home',
                'menu_order' => 0,
                'menu_status' => 1, // Ini akan punya daftar UseCase
            ]
        );

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

        // 👇👇👇 START: ADD UAT, REPORT, DATABASE DATA FOR "Informasi Umum" UseCase 👇👇👇

        UatData::firstOrCreate(
            ['use_case_id' => $infoUmumEpesantren->id, 'nama_proses_usecase' => 'Verifikasi Tampilan Informasi'],
            [
                'keterangan_uat' => 'Memastikan semua elemen informasi umum tampil dengan benar.',
                'status_uat' => 'success',
                'gambar_uat' => '<img src="/ckeditor/images/example_uat_info.png" alt="Contoh Tampilan Informasi Umum">'
            ]
        );
        ReportData::firstOrCreate(
            ['use_case_id' => $infoUmumEpesantren->id, 'nama_report' => 'Log Akses Informasi'],
            [
                'aktor' => 'Sistem',
                'keterangan' => 'Mencatat setiap akses ke halaman informasi umum.',
            ]
        );
        DatabaseData::firstOrCreate(
            ['use_case_id' => $infoUmumEpesantren->id, 'keterangan' => 'Struktur Data Informasi Umum'],
            [
                'gambar_database' => '<img src="/ckeditor/images/example_db_info.png" alt="Contoh Struktur DB Informasi Umum">',
                'relasi' => 'Tidak ada relasi kompleks langsung; data statis.',
            ]
        );

        // 👆👆👆 END: ADD UAT, REPORT, DATABASE DATA FOR "Informasi Umum" UseCase 👆👆👆


        // Buat menu untuk "Kelas" di kategori "epesantren"
        $kelasMenu = NavMenu::firstOrCreate(
            ['menu_nama' => 'Kelas', 'category' => 'epesantren', 'menu_child' => 0],
            [
                'menu_link' => 'epesantren/kelas',
                'menu_icon' => 'fa-solid fa-graduation-cap',
                'menu_order' => 1,
                'menu_status' => 1, // Ini akan punya daftar UseCase
            ]
        );

        // Buat UseCase "Manajemen Kelas" untuk menu "Kelas"
        $useCaseKelas = UseCase::firstOrCreate(
            ['menu_id' => $kelasMenu->menu_id, 'usecase_id' => 'UC-' . $kelasMenu->menu_id . '-001'],
            [
                'nama_proses' => 'Manajemen Kelas',
                'deskripsi_aksi' => 'Memfasilitasi administrator untuk mengelola data kelas, termasuk menambah, mengedit, dan menghapus kelas.',
                'aktor' => 'Admin ePesantren',
                'tujuan' => 'Memastikan data kelas selalu up-to-date.',
                'kondisi_awal' => 'Admin masuk ke modul Manajemen Kelas.',
                'kondisi_akhir' => 'Data kelas berhasil diperbarui di sistem.',
                'aksi_reaksi' => 'Admin melakukan aksi manajemen kelas.',
                'reaksi_sistem' => 'Sistem memperbarui database dan menampilkan notifikasi.',
            ]
        );

        // Tambah data UAT untuk UseCase "Manajemen Kelas" (Existing, already correct)
        UatData::firstOrCreate(
            ['use_case_id' => $useCaseKelas->id, 'nama_proses_usecase' => 'Tambah Data Kelas'],
            [
                'keterangan_uat' => 'Memvalidasi data kelas baru dan menyimpannya.',
                'status_uat' => 'success',
                'gambar_uat' => '<img src="/ckeditor/images/example_uat_tambah.png" alt="UAT Tambah Data Kelas">'
            ]
        );
        UatData::firstOrCreate(
            ['use_case_id' => $useCaseKelas->id, 'nama_proses_usecase' => 'Edit Data Kelas'],
            [
                'keterangan_uat' => 'Memperbarui informasi kelas yang sudah ada di database.',
                'status_uat' => 'success',
                'gambar_uat' => '<img src="/ckeditor/images/example_uat_edit.png" alt="UAT Edit Data Kelas">'
            ]
        );
        UatData::firstOrCreate(
            ['use_case_id' => $useCaseKelas->id, 'nama_proses_usecase' => 'Hapus Data Kelas'],
            [
                'keterangan_uat' => 'Menghapus data kelas dari sistem secara permanen.',
                'status_uat' => 'success',
                'gambar_uat' => '<img src="/ckeditor/images/example_uat_hapus.png" alt="UAT Hapus Data Kelas">'
            ]
        );

        // Tambah data Report untuk UseCase "Manajemen Kelas" (Existing, already correct)
        ReportData::firstOrCreate(
            ['use_case_id' => $useCaseKelas->id, 'nama_report' => 'Laporan Harian Kelas'],
            [
                'aktor' => 'Sistem',
                'keterangan' => 'Laporan ringkasan aktivitas kelas harian.',
            ]
        );

        // Tambah data Database untuk UseCase "Manajemen Kelas" (Existing, already correct)
        DatabaseData::firstOrCreate(
            ['use_case_id' => $useCaseKelas->id, 'keterangan' => 'Skema Tabel Kelas'],
            [
                'gambar_database' => '<img src="/ckeditor/images/example_db_kelas.png" alt="Skema Tabel Kelas">',
                'relasi' => 'Tabel kelas berelasi dengan tabel siswa dan guru.',
            ]
        );

        // Buat menu untuk "Pengaturan" di kategori "epesantren" (sebagai folder)
        NavMenu::firstOrCreate(
            ['menu_nama' => 'Pengaturan', 'category' => 'epesantren', 'menu_child' => 0],
            [
                'menu_link' => 'epesantren/pengaturan',
                'menu_icon' => 'fa-solid fa-gear',
                'menu_order' => 2,
                'menu_status' => 0, // Ini adalah folder, tidak punya daftar UseCase
            ]
        );

        // === Kategori Baru: Kepegawaian (Contoh) ===
        $homeMenuKepegawaian = NavMenu::firstOrCreate(
            ['menu_nama' => 'Beranda Kepegawaian', 'category' => 'kepegawaian', 'menu_child' => 0],
            [
                'menu_link' => 'kepegawaian/beranda-kepegawaian',
                'menu_icon' => 'fa-solid fa-users',
                'menu_order' => 0,
                'menu_status' => 1, // Ini akan punya daftar UseCase
            ]
        );
        // Buat UseCase "Informasi Umum" untuk "Beranda Kepegawaian"
        UseCase::firstOrCreate(
            ['menu_id' => $homeMenuKepegawaian->menu_id, 'usecase_id' => 'UC-' . $homeMenuKepegawaian->menu_id . '-001'],
            [
                'nama_proses' => 'Informasi Umum',
                'deskripsi_aksi' => '# Selamat Datang di Dokumentasi Kepegawaian' . "\n\nIni adalah halaman beranda untuk modul kepegawaian.",
                'aktor' => 'Sistem',
                'tujuan' => 'Menyediakan ringkasan modul kepegawaian.',
                'kondisi_awal' => 'Pengguna mengakses kategori kepegawaian.',
                'kondisi_akhir' => 'Ringkasan modul ditampilkan.',
                'aksi_reaksi' => 'Pengguna menjelajahi menu.',
                'reaksi_sistem' => 'Sistem menampilkan navigasi terkait.',
            ]
        );

        $dataPegawaiMenu = NavMenu::firstOrCreate(
            ['menu_nama' => 'Data Pegawai', 'category' => 'kepegawaian', 'menu_child' => 0],
            [
                'menu_link' => 'kepegawaian/data-pegawai',
                'menu_icon' => 'fa-solid fa-user-tie',
                'menu_order' => 1,
                'menu_status' => 1, // Ini akan punya daftar UseCase
            ]
        );
        // Buat UseCase "Pencatatan Data Pegawai"
        UseCase::firstOrCreate(
            ['menu_id' => $dataPegawaiMenu->menu_id, 'usecase_id' => 'UC-' . $dataPegawaiMenu->menu_id . '-001'],
            [
                'nama_proses' => 'Pencatatan Data Pegawai',
                'deskripsi_aksi' => 'Mencatat dan mengelola detail informasi setiap pegawai.',
                'aktor' => 'Admin Kepegawaian',
                'tujuan' => 'Memastikan data pegawai lengkap dan akurat.',
                'kondisi_awal' => 'Admin membuka form pencatatan pegawai.',
                'kondisi_akhir' => 'Data pegawai tersimpan di sistem.',
                'aksi_reaksi' => 'Admin memasukkan data.',
                'reaksi_sistem' => 'Sistem memvalidasi dan menyimpan.',
            ]
        );
    }
}