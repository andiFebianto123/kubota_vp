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
            $table->id();
            $table->string('ds_num');
            $table->integer('ds_line');
            $table->string('reason_num');
            $table->string('repair_num');
            $table->dateTime('repair_date');
            $table->string('repair_type');
            $table->double('repair_qty');
            $table->string('ref_num');
            $table->integer('ref_line');
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
        Schema::dropIfExists('delivery_repairs');
    }
}
