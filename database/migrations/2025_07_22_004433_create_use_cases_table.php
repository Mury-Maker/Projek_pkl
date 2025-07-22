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
        Schema::create('use_cases', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('menu_id'); // Foreign key ke navmenu
            $table->string('usecase_id')->nullable()->comment('ID UseCase seperti di video, misal: UC-TAMBAH-SISWA');
            $table->string('nama_proses'); // Nama tindakan, misal: "Tambah Siswa"
            $table->text('deskripsi_aksi')->nullable();
            $table->string('aktor')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('kondisi_awal')->nullable();
            $table->text('kondisi_akhir')->nullable();
            $table->text('aksi_reaksi')->nullable();
            $table->text('reaksi_sistem')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('menu_id')->references('menu_id')->on('navmenu')->onDelete('cascade');
            
            // HAPUS ATAU KOMENTARI BARIS INI: $table->unique('menu_id');
            // Karena satu menu_id kini bisa punya banyak use_cases
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('use_cases');
    }
};