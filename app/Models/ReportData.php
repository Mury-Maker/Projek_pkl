<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportData extends Model
{
    use HasFactory;

    protected $table = 'report_data';
    protected $primaryKey = 'id_report'; // ✅ Correct
    protected $fillable = [
        'use_case_id',
        'aktor',
        'nama_report',
        'keterangan',
    ];

    public function useCase()
    {
        return $this->belongsTo(UseCase::class);
    }
}