<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryRejectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_rejects', function (Blueprint $table) {
            $table->id();
            $table->string('ds_num');
            $table->integer('ds_line');
            $table->string('reason_num');
            $table->string('reason');
            $table->double('rejected_qty');
            $table->dateTime('inspection_date');
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
        Schema::dropIfExists('delivery_rejects');
    }
}
