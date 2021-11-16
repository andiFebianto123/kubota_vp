<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssuedMaterialOuthousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issued_material_outhouses', function (Blueprint $table) {
            $table->id();
            $table->string('ds_num');
            $table->integer('ds_line');
            $table->integer('ds_detail');
            $table->string('matl_item');
            $table->string('description');
            $table->string('lot');
            $table->double('issue_qty');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('issued_material_outhouses');
    }
}
