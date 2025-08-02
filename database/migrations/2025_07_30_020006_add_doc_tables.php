<?php

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
        Schema::create('doc_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('menu_id'); //Foreign key ke navmenu_id
            $table->string('nama_tabel');
            $table->timestamps();

            // Foreign Key
            $table->foreign('menu_id')->references('menu_id')->on('navmenu')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_tables');
    }
};
