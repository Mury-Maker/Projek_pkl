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
        Schema::create('docs', function (Blueprint $table) {
            $table->id('docs_id');
            $table->unsignedInteger('menu_id');
            $table->string('title')->default('Default'); // Added in a separate migration, but here for completeness
            $table->text('content');
            $table->timestamps();
            
            $table->foreign('menu_id')->references('menu_id')->on('navmenu')->onDelete('cascade');
            $table->unique(['menu_id', 'title']); // Add unique constraint here
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
