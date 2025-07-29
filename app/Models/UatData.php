<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UatData extends Model
{
    use HasFactory;

    protected $table = 'uat_data';
    protected $primaryKey = 'id_uat'; // ✅ Correct
    protected $fillable = [
        'use_case_id',
        'nama_proses_usecase',
        'keterangan_uat',
        'status_uat',
    ];

    public function uatImages(){
        return $this->hasMany(UAT_IMAGES::class);
    }

    public function useCase()
    {
        return $this->belongsTo(UseCase::class);
    }
}