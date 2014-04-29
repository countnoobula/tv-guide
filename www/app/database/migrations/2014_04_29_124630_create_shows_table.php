<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShowsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('shows', function($table) {
	        $table->increments('id');
	        $table->integer('channel_id')->references('id')->on('channels');
	        $table->dateTime('starting_time');
	        $table->string('title');
	        $table->string('episode_title');
	        $table->string('country');
	        $table->string('genre');
	        $table->string('parental_rating');
	        $table->string('performer');
	        $table->string('regie');
	        $table->longText('story_middle');
	        $table->string('year');
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
		Schema::drop('shows');
	}

}
