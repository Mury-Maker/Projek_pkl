<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocTables extends Model
{
    protected $fillable = ['menu_id', 'nama_tabel'];

    public function columns()
    {
        return $this->hasMany(DocColumns::class, 'table_id');
    }

    public function foreignKeysFrom()
    {
        return $this->hasMany(DocRelations::class, 'from_tableid');
    }

    public function navmenu()
    {
        return $this->belongsTo(Navmenu::class, 'menu_id', 'menu_id');
    }
}

