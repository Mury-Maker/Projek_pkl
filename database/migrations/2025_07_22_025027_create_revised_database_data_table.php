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
        Schema::create('database_data', function (Blueprint $table) {
            $table->id('id_database'); // Kolom id_database
            $table->foreignId('use_case_id')->constrained('use_cases')->onDelete('cascade');
            $table->text('keterangan')->nullable(); // Kolom keterangan
            $table->longText('gambar_database')->nullable(); // Menggunakan LONGTEXT untuk gambar CKEditor (HTML/base64)
            $table->text('relasi')->nullable(); // Kolom relasi (teks)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_data');
    }
};