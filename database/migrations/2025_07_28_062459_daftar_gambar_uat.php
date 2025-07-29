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
        Schema::create('uats_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uats_id');
            $table->text('link');
            $table->timestamps();

            // Foreign Key
            $table->foreign('uats_id')->references('id_uat')->on('uat_data')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uats_images');
    }
};
