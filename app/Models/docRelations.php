<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocRelations extends Model
{
    protected $fillable = ['from_tableid', 'from_columnid', 'to_tableid', 'to_columnid'];

}

