<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldInIssuedMaterialOuthouse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('issued_material_outhouse', function (Blueprint $table) {
            $table->string('po_num')->after('matl_item')->length(10);
            $table->integer('po_line')->after('po_num');
            $table->string('ds_type')->after('po_line')->default('00');
            $table->string('vend_num')->after('ds_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('issued_material_outhouse', function (Blueprint $table) {
            //
        });
    }
}
