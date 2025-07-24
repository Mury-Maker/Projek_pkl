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
        Schema::create('report_data', function (Blueprint $table) {
            $table->id('id_report'); // Kolom id_report
            $table->foreignId('use_case_id')->constrained('use_cases')->onDelete('cascade');
            $table->string('aktor'); // Kolom aktor
            $table->string('nama_report'); // Kolom nama_report
            $table->text('keterangan')->nullable(); // Kolom keterangan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_data');
    }
};