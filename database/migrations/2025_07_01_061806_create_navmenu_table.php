<?php
// File: database/migrations/...._create_navmenu_table.php
// PERBAIKAN: Menambahkan ->nullable() pada menu_icon

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('navmenu', function (Blueprint $table) {
            $table->increments('menu_id');
            $table->string('menu_nama', 50)->nullable();
            $table->string('menu_link', 100);
            $table->string('menu_icon', 30)->default('fa-regular fa-circle')->nullable(); // <-- DIPERBAIKI DI SINI
            $table->unsignedInteger('menu_child')->default(0);
            $table->integer('menu_order')->default(0);
            $table->boolean('menu_status')->default(true);
            $table->string('category', 50)->default('adminsekolah')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navmenu');
    }
};
