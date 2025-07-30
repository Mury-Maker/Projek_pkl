<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UAT_IMAGES extends Model
{
    
    protected $table = 'uats_images';

    protected $fillable = [
        'uats_id',
        'link',
        'created_at',
        'updated_at',
    ];

    public function uats(){
        return $this->belongsTo(UatData::class);
    }
}
