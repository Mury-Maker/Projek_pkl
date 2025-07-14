<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('docs', function (Blueprint $table) {
            // Check if column exists before adding it to prevent errors on re-run
            if (!Schema::hasColumn('docs', 'title')) {
                $table->string('title')->default('Default')->after('menu_id');
            }
        });
    }
    
    public function down()
    {
        Schema::table('docs', function (Blueprint $table) {
            // Check if column exists before dropping it
            if (Schema::hasColumn('docs', 'title')) {
                $table->dropColumn('title');
            }
        });
    }
};
