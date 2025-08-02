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
        Schema::create('doc_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('to_tableid'); //Foreign key ke navmenu_id
            $table->unsignedBigInteger('from_tableid'); //Foreign key ke navmenu_id
            $table->string('to_columnid');
            $table->string('from_columnid');
            $table->timestamps();

            // Foreign Key
            $table->foreign('to_tableid')->references('id')->on('doc_tables')->onDelete('cascade');
            $table->foreign('from_tableid')->references('id')->on('doc_tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_relations');
    }
};
