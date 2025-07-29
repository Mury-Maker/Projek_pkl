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
        Schema::create('databases_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('databases_id');
            $table->text('link');
            $table->timestamps();

            // Foreign Key
            $table->foreign('databases_id')->references('id_database')->on('database_data')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
