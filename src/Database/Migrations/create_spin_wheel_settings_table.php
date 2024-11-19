<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('spin_wheel_settings', function (Blueprint $table) {
			$table->id();
			$table->json('prizes')->nullable();
			$table->json('config')->nullable();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('spin_wheel_settings');
	}
};
