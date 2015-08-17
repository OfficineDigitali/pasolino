<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
	public function up()
	{
		Schema::create('projects', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('sender_host');
			$table->string('render_string', 1000);
			$table->string('extension', 4);
			$table->enum('status', ['undefined', 'failed', 'testing', 'fetching', 'rendering', 'ready']);
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('projects');
	}
}
