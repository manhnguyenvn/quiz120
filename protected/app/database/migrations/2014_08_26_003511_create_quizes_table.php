<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuizesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::create('quizes', function($table)
		{
			$table->increments('id');
			$table->string('topic')->index();
			$table->string('image');
			$table->text('description');
			$table->text('pageContent');
			$table->string('type');
			$table->text('questions');
			$table->text('results');
			$table->text('ogImages');
			$table->boolean('active')->default(false)->index();
			$table->timestamps();
			$table->index('created_at');
			$table->index('updated_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
		Schema::drop('quizes');
	}

}
