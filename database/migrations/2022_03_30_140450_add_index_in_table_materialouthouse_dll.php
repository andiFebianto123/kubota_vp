<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexInTableMaterialouthouseDll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('material_outhouse', function (Blueprint $table) {
            $table->index('po_num');
            $table->index('po_line');
            $table->index('matl_item');
            $table->index('lot');
            $table->index('lot_qty');
            $table->index('qty_per');
        });
        Schema::table('po', function (Blueprint $table) {
            $table->index('po_num');
            $table->index('vend_num');
            $table->index('po_date');
            $table->index('po_change');
            $table->index('email_flag');
        });
        Schema::table('po_line', function (Blueprint $table) {
            // $table->index('po_line');
            $table->index('po_release');
            $table->index('po_change');
            $table->index('po_change_date');
            $table->index('item');
            $table->index('due_date');
            $table->index('unit_price');
            $table->index('status');
            $table->index('accept_flag');
            $table->index('outhouse_flag');
        });
        Schema::table('issued_material_outhouse', function(Blueprint $table){
            $table->index('ds_num');
            $table->index('ds_line');
            $table->index('ds_detail');
            $table->index('matl_item');
            $table->index('lot');
            $table->index('issue_qty');
        });
        Schema::table('delivery', function(Blueprint $table){
            $table->index('ds_num');
            $table->index('ds_line');
            $table->index('ds_type');
            $table->index('po_num');
            $table->index('po_line');
            $table->index('item');
            $table->index('po_release');
            $table->index('po_change');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('material_outhouse', function (Blueprint $table) {
            $table->dropIndex(['po_num']); // Drops index
            $table->dropIndex(['po_line']); // Drops index
            $table->dropIndex(['matl_item']); // Drops index
            $table->dropIndex(['lot']); // Drops index
            $table->dropIndex(['lot_qty']); // Drops index
            $table->dropIndex(['qty_per']); // Drops index
        });
        Schema::table('po', function (Blueprint $table) {
            $table->dropIndex(['po_num']); // Drops index
            $table->dropIndex(['vend_num']); // Drops index
            $table->dropIndex(['po_date']); // Drops index
            $table->dropIndex(['po_change']); // Drops index
            $table->dropIndex(['email_flag']); // Drops index
        });
        Schema::table('po_line', function (Blueprint $table) {
            $table->dropIndex(['po_line']);
            $table->dropIndex(['po_release']);
            $table->dropIndex(['po_change']);
            $table->dropIndex(['po_change_date']);
            $table->dropIndex(['item']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['unit_price']);
            $table->dropIndex(['status']);
            $table->dropIndex(['accept_flag']);
            $table->dropIndex(['outhouse_flag']);
        });
        Schema::table('issued_material_outhouse', function(Blueprint $table){
            $table->dropIndex(['ds_num']);
            $table->dropIndex(['ds_line']);
            $table->dropIndex(['ds_detail']);
            $table->dropIndex(['matl_item']);
            $table->dropIndex(['lot']);
            $table->dropIndex(['issue_qty']);
        });
        Schema::table('delivery', function(Blueprint $table){
            $table->dropIndex(['ds_num']);
            $table->dropIndex(['ds_line']);
            $table->dropIndex(['ds_type']);
            $table->dropIndex(['po_num']);
            $table->dropIndex(['po_line']);
            $table->dropIndex(['item']);
            $table->dropIndex(['po_release']);
            $table->dropIndex(['po_change']);
        });
    }
}
