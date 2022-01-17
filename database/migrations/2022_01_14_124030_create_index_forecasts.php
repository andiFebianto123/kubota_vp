<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndexForecasts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forecasts', function (Blueprint $table) {
            $table->index('forecast_date');
            $table->index('vend_num');
            $table->index('item');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forecasts', function (Blueprint $table) {
            $table->dropIndex('forecast_date');
            $table->dropIndex('vend_num');
            $table->dropIndex('item');
        });
    }
}
