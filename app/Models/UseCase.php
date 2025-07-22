<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UseCase extends Model
{
    use HasFactory;

    protected $table = 'use_cases';
    protected $fillable = [
        'menu_id',
        'usecase_id',
        'nama_proses',
        'deskripsi_aksi',
        'aktor',
        'tujuan',
        'kondisi_awal',
        'kondisi_akhir',
        'aksi_reaksi',
        'reaksi_sistem',
    ];

    public function menu()
    {
        return $this->belongsTo(NavMenu::class, 'menu_id', 'menu_id');
    }

    public function uatData()
    {
        return $this->hasMany(UatData::class, 'use_case_id', 'id');
    }

    public function reportData() // Perubahan dari pengkodeanData()
    {
        return $this->hasMany(ReportData::class, 'use_case_id', 'id');
    }

    public function databaseData()
    {
        return $this->hasMany(DatabaseData::class, 'use_case_id', 'id');
    }
}