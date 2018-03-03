<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('campaigns', function(Blueprint $table){
            $table->string('background_color')->nullable();
            $table->string('foreground_color')->nullable();
            $table->string('accent_color')->nullable();
            $table->string('font')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('campaigns', function(Blueprint $table) {
            $table->dropColumn('background_color');
            $table->dropColumn('foreground_color');
            $table->dropColumn('accent_color');
            $table->dropColumn('font');
        });
    }
}
