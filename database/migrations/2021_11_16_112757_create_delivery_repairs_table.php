<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryRepairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_repairs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ds_num');
            $table->integer('ds_line');
            $table->string('reason_num');
            $table->string('repair_num');
            $table->dateTime('repair_date');
            $table->string('repair_type');
            $table->double('repair_qty');
            $table->string('ref_num');
            $table->integer('ref_line');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
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
        Schema::dropIfExists('delivery_repairs');
    }
}
