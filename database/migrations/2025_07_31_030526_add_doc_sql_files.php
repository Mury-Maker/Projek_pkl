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
        Schema::create('doc_sql_files', function (Blueprint $table) {
        $table->id();
        $table->unsignedInteger('navmenu_id');
        $table->string('file_name');
        $table->string('file_path');
        $table->timestamps();

        $table->foreign('navmenu_id')->references('menu_id')->on('navmenu')->onDelete('cascade');
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
