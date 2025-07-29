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
        Schema::create('uat_data', function (Blueprint $table) {
            $table->id('id_uat'); // <-- LANGSUNG DEFINISIKAN PRIMARY KEY SEBAGAI id_uat
            $table->foreignId('use_case_id')->constrained('use_cases')->onDelete('cascade');
            $table->string('nama_proses_usecase'); // <-- LANGSUNG GUNAKAN NAMA KOLOM YANG BENAR
            $table->text('keterangan_uat')->nullable();
            $table->string('status_uat')->nullable(); // Misal: 'success', 'failed', 'pending'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uat_data');
    }
};