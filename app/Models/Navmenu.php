<?php
// File: app/Models/NavMenu.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class NavMenu extends Model
{
    use HasFactory;

    protected $table = 'navmenu';
    protected $primaryKey = 'menu_id';
    public $timestamps = false;

    protected $fillable = [
        'menu_nama',
        'menu_link',
        'menu_icon',
        'menu_child',
        'menu_order',
        'menu_status', // 0 = folder, 1 = memiliki daftar use case
        'category',
    ];

    /**
     * Relasi ke UseCase (koleksi use case yang termasuk dalam menu ini).
     */
    public function useCases() // Perubahan dari useCase() menjadi useCases() (plural)
    {
        return $this->hasMany(UseCase::class, 'menu_id', 'menu_id')->orderBy('nama_proses'); // Urutkan berdasarkan nama proses
    }

    /**
     * Relasi ke parent menu.
     */
    public function parent()
    {
        return $this->belongsTo(NavMenu::class, 'menu_child', 'menu_id');
    }

    /**
     * Relasi ke children (sub-menu).
     */
    public function children()
    {
        return $this->hasMany(NavMenu::class, 'menu_child', 'menu_id')->orderBy('menu_order');
    }

    /**
     * Membangun menu hierarkis dari koleksi.
     */
    public static function buildTree(Collection $elements, $parentId = 0): array
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element->menu_child == $parentId) {
                $item = clone $element;

                // menu_link untuk item navigasi akan selalu menunjuk ke halaman "index tindakan"
                $pageSlug = Str::slug($item->menu_nama);
                $item->menu_link = route('docs', ['category' => $item->category, 'page' => $pageSlug]);
                
                $children = self::buildTree($elements, $item->menu_id);
                
                $item->children = $children;
                unset($item->relations['children']);

                $branch[] = $item;
            }
        }

        usort($branch, function($a, $b) {
            return $a->menu_order <=> $b->menu_order;
        });

        return $branch;
    }

    public function isDescendantOf($potentialParentId): bool
    {
        $current = $this;
        while ($current->menu_child !== 0 && $current->menu_child !== null) {
            if ($current->menu_child == $potentialParentId) {
                return true;
            }
            $current = $current->parent;
            if (!$current) break;
        }
        return false;
    }
}