<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseData extends Model
{
    use HasFactory;

    protected $table = 'database_data';
    protected $primaryKey = 'id_database'; // ✅ Correct
    protected $fillable = [
        'use_case_id',
        'keterangan',
        'gambar_database',
        'relasi',
    ];

    public function useCase()
    {
        return $this->belongsTo(UseCase::class);
    }
}