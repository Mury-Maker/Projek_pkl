<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocSqlFile extends Model
{
    protected $fillable = ['navmenu_id', 'file_name', 'file_path'];

    public function navmenu()
    {
        return $this->belongsTo(Navmenu::class, 'navmenu_id', 'menu_id');
    }
}
