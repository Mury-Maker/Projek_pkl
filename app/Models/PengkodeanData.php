<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengkodeanData extends Model
{
    use HasFactory;

    protected $table = 'pengkodean_data';
    protected $fillable = [
        'use_case_id',
        'nama_proses_pengkodean',
        'keterangan_pengkodean',
        'status_pengkodean',
        'gambar_pengkodean_paths',
    ];

    protected $casts = [
        'gambar_pengkodean_paths' => 'array',
    ];

    public function useCase()
    {
        return $this->belongsTo(UseCase::class);
    }
}