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
            $table->id();
            $table->string('forecast_num');
            $table->integer('forecast_change')->nullable();
            $table->dateTime('forecast_date');
            $table->string('vend_num');
            $table->integer('item');
            $table->string('description');
            $table->string('u_m');
            $table->dateTime('due_date');
            $table->dateTime('production_date');
            $table->integer('qty');
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
        Schema::dropIfExists('forecasts');
    }
}
