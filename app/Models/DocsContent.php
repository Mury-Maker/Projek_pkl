<?php
// File: app/Models/DocsContent.php
// PERBAIKAN: Menambahkan 'title' ke $fillable

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocsContent extends Model
{
    use HasFactory;

    protected $table = 'docs';
    protected $primaryKey = 'docs_id';

    /**
     * Atribut yang boleh diisi secara massal.
     */
    protected $fillable = [
        'content',
        'menu_id',
        'title', // Tambahkan ini
    ];

    /**
     * Relasi kembali ke menu-nya.
     */
    public function menu()
    {
        return $this->belongsTo(NavMenu::class, 'menu_id', 'menu_id');
    }
}