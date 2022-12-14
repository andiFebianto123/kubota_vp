<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForecastsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('forecast_num');
            $table->integer('forecast_change')->nullable();
            $table->dateTime('forecast_date')->nullable();
            $table->string('vend_num')->nullable();
            $table->string('item')->nullable();
            $table->string('description')->nullable();
            $table->string('u_m')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('production_date')->nullable();
            $table->integer('qty')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forecasts');
    }
}
