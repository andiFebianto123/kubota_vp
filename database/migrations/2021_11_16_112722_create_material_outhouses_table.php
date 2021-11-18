<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialOuthousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_outhouses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('instruction_num');
            $table->string('po_numb');
            $table->integer('po_line');
            $table->integer('seq');
            $table->string('matl_item');
            $table->string('description');
            $table->integer('lot_seq');
            $table->string('lot');
            $table->double('lot_qty');
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
        Schema::dropIfExists('material_outhouses');
    }
}
