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
        Schema::create('material_outhouse', function (Blueprint $table) {
            $table->integer('id');
            $table->string('instruction_num')->length(15);
            $table->string('po_num')->length(10);
            $table->integer('po_line');
            $table->integer('seq');
            $table->string('matl_item');
            $table->string('description');
            $table->integer('lot_seq');
            $table->string('lot');
            $table->double('lot_qty');
            $table->double('qty_per');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->primary(['id', 'instruction_num', 'seq', 'lot_seq']);
        });
        Schema::table('material_outhouse', function (Blueprint $table) {
            $table->integer('id', true, true)->change();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('material_outhouse');
    }
}
