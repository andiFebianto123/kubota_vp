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
        Schema::create('issued_material_outhouse', function (Blueprint $table) {
            $table->integer('id');
            $table->string('ds_num')->length(15);
            $table->integer('ds_line');
            $table->integer('ds_detail');
            $table->string('matl_item');
            $table->string('description');
            $table->string('lot');
            $table->double('issue_qty');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->primary(['id', 'ds_num', 'ds_line', 'ds_detail']);
        });
        Schema::table('issued_material_outhouse', function (Blueprint $table) {
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
        Schema::dropIfExists('issued_material_outhouse');
    }
}
