<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocColumns extends Model
{
    protected $fillable = ['table_id', 'nama_kolom', 'tipe', 'is_primary', 'is_nullable', 'is_unique'];
}

