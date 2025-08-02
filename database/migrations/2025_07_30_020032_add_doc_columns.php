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
        Schema::create('doc_columns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id'); //Foreign key ke id milik doc_tables
            $table->string('nama_kolom');
            $table->string('tipe');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_nullable')->default(true);
            $table->boolean('is_unique')->default(false);
            $table->timestamps();

            // Foreign Key
            $table->foreign('table_id')->references('id')->on('doc_tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_columns');
    }
};
