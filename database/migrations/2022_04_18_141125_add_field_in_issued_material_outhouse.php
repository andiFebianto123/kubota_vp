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
            if (!Schema::hasColumn('issued_material_outhouse', 'po_num')) {
                $table->string('po_num')->after('matl_item')->length(10);
            } 
            if(!Schema::hasColumn('issued_material_outhouse', 'po_line')){
                $table->integer('po_line')->after('po_num');
            }
            if(!Schema::hasColumn('issued_material_outhouse', 'ds_type')){
                $table->string('ds_type')->after('po_line')->default('00');
            }
            if(!Schema::hasColumn('issued_material_outhouse', 'vend_num')){
                $table->string('vend_num')->after('ds_type');
            }
            if(!Schema::hasColumn('issued_material_outhouse', 'shipped_date')){
                // $table->dateTime('shipped_date')->after('issue_qty')->nullable();
            }
            // $table->index(['shipped_date', 'ds_type']);
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
