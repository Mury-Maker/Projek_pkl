<?php
// File: database/seeders/EpesantrenNavmenuSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EpesantrenNavmenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offset = DB::table('navmenu')->max('menu_id') ?? 0;

        $epesantrenMenuData = [
            ['menu_id' => 1, 'menu_nama' => 'Dashboard', 'menu_link' => 'manage', 'menu_icon' => 'fa fa-th', 'menu_child' => 0, 'menu_order' => 1, 'menu_status' => 0],
            ['menu_id' => 2, 'menu_nama' => 'Daftar Tabel', 'menu_link' => '#', 'menu_icon' => 'fa fa-users text-stock', 'menu_child' => 0, 'menu_order' => 2, 'menu_status' => 0],
            // ... (semua 223 baris data Anda yang lain ada di sini) ...
            ['menu_id' => 230, 'menu_nama' => 'Pelanggaran', 'menu_link' => 'manage/pelanggaran', 'menu_icon' => 'fa fa-circle-o', 'menu_child' => 95, 'menu_order' => 5, 'menu_status' => 0]
        ];
        
        $dokumentasiMenuData = [
            ['menu_id' => 401, 'menu_nama' => 'Dok. ePesantren', 'menu_link' => '#', 'menu_icon' => 'fa fa-book text-success', 'menu_child' => 0, 'menu_order' => 101, 'menu_status' => 0],
            ['menu_id' => 402, 'menu_nama' => 'Pendahuluan', 'menu_link' => 'docs/epesantren/welcome', 'menu_icon' => 'fa fa-circle-o', 'menu_child' => 401, 'menu_order' => 1, 'menu_status' => 0],
            ['menu_id' => 403, 'menu_nama' => 'Panduan Kesantrian', 'menu_link' => 'docs/epesantren/kesantrian', 'menu_icon' => 'fa fa-circle-o', 'menu_child' => 401, 'menu_order' => 2, 'menu_status' => 0],
            ['menu_id' => 404, 'menu_nama' => 'Panduan Keuangan', 'menu_link' => 'docs/epesantren/keuangan', 'menu_icon' => 'fa fa-circle-o', 'menu_child' => 401, 'menu_order' => 3, 'menu_status' => 0],
            ['menu_id' => 405, 'menu_nama' => 'Panduan Kepengasuhan', 'menu_link' => 'docs/epesantren/kepengasuhan', 'menu_icon' => 'fa fa-circle-o', 'menu_child' => 401, 'menu_order' => 4, 'menu_status' => 0],
            // Contoh menambahkan child di dalam child
            // 'Panduan Kesantrian' (ID 403) akan menjadi parent untuk 'Sub Bagian 1 Kesantrian'
            ['menu_id' => 406, 'menu_nama' => 'Sub Bagian 1 Kesantrian', 'menu_link' => 'docs/epesantren/kesantrian/sub1', 'menu_icon' => 'fa fa-angle-right', 'menu_child' => 403, 'menu_order' => 1, 'menu_status' => 0],
            // 'Sub Bagian 1 Kesantrian' (ID 406) akan menjadi parent untuk 'Detail Sub 1'
            ['menu_id' => 407, 'menu_nama' => 'Detail Sub 1', 'menu_link' => 'docs/epesantren/kesantrian/sub1/detail', 'menu_icon' => 'fa fa-dot-circle', 'menu_child' => 406, 'menu_order' => 1, 'menu_status' => 0],
        ];

        $finalData = array_merge($epesantrenMenuData, $dokumentasiMenuData);

        $transformedData = array_map(function($item) use ($offset) {
            $item['menu_id'] += $offset;
            if ($item['menu_child'] != 0) {
                $item['menu_child'] += $offset;
            }
            $item['category'] = 'epesantren'; 
            return $item;
        }, $finalData); 

        DB::table('navmenu')->where('category', 'epesantren')->delete();
        DB::table('navmenu')->insert($transformedData);
    }
}