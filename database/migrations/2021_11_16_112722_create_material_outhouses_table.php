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
            $table->id();
            $table->string('instruction_num');
            $table->string('po_numb');
            $table->integer('po_line');
            $table->integer('seq');
            $table->string('matl_item');
            $table->string('description');
            $table->integer('lot_seq');
            $table->string('lot');
            $table->double('lot_qty');
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
        Schema::dropIfExists('material_outhouses');
    }
}
