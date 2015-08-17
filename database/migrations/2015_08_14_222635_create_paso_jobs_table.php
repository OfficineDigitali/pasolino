<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePasoJobsTable extends Migration
{
	public function up()
	{
		Schema::create('paso_jobs', function (Blueprint $table) {
			$table->increments('id');
			$table->enum('type', ['testing', 'fetching', 'rendering']);
			$table->integer('project_id');
			$table->integer('percentage');
			$table->string('error');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('paso_jobs');
	}
}
