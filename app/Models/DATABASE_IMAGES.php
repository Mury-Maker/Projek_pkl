<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DATABASE_IMAGES extends Model
{
    
    protected $table = 'databases_images';

    protected $primaryKey = 'id';

    protected $fillable = [
        'databases_id',
        'link',
        'created_at',
        'updated_at',
    ];

    public function uats(){
        return $this->belongsTo(UatData::class);
    }


    public function databases(){
        return $this->belongsTo(UseCase::class);
    }
}
