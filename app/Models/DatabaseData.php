<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseData extends Model
{
    use HasFactory;

    protected $table = 'database_data';
    protected $primaryKey = 'id_database';
    protected $fillable = [
        'use_case_id',
        'keterangan',
        'relasi',
    ];

    public function databaseImage(){
        return $this->hasMany(DATABASE_IMAGES::class);
    }

    public function useCase()
    {
        return $this->belongsTo(UseCase::class);
    }
}