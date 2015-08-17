<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
	public function up()
	{
		Schema::create('files', function (Blueprint $table) {
			$table->increments('id');
			$table->string('filename');
			$table->string('original_path');
			$table->string('current_path');
			$table->integer('references');
			$table->enum('status', ['failed', 'fetching', 'ready']);
			$table->integer('project_id');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('files');
	}
}
