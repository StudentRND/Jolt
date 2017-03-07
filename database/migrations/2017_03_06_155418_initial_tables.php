<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InitialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('users', function(Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->string('email');
            $table->text('password');
            $table->boolean('is_superadmin')->default(false);
            $table->timestamps();
        });

        \Schema::create('campaigns', function(Blueprint $table) {
            $table->increments('id');
            $table->string('invite');
            $table->string('name');
            $table->text('welcome')->nullable();
            $table->string('url');
            $table->string('domain')->nullable();
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->timestamps();
        });

        \Schema::create('campaigns_updates', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->text('text');
            $table->timestamps();
        });

        \Schema::create('users_campaigns', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('campaign_id')->unsigned();
            $table->boolean('is_admin')->default(true);
            $table->timestamps();
        });

        \Schema::create('links', function(Blueprint $table) {
            $table->string('id');
            $table->integer('campaign_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->enum('type', ['twitter', 'facebook', 'linkedin', 'instagram', 'reddit', 'tumblr', 'email', 'other']);
            $table->string('description');
            $table->string('url');
            $table->timestamps();
        });

        \Schema::create('links_clicks', function(Blueprint $table) {
            $table->increments('id');
            $table->string('link_id');
            $table->string('lat');
            $table->string('lng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('links_clicks');
        \Schema::drop('links');
        \Schema::drop('users_campaigns');
        \Schema::drop('campaigns_updates');
        \Schema::drop('campaigns');
        \Schema::drop('users');
    }
}
