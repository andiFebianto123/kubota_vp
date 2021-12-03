<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery', function (Blueprint $table) {
            $table->integer('id');
            $table->string('ds_num')->length(15);
            $table->integer('ds_line');
            $table->string('ds_type')->default('00');
            $table->string('po_num');
            $table->integer('po_line');
            $table->string('item')->nullable();
            $table->integer('po_release')->nullable();
            $table->string('description')->nullable();
            $table->double('order_qty')->nullable();
            $table->integer('w_serial')->default(0);
            $table->string('u_m')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->double('unit_price')->nullable();
            $table->string('wh')->nullable();
            $table->string('location')->nullable();
            $table->string('tax_status')->nullable();
            $table->string('currency')->nullable();
            $table->double('shipped_qty')->nullable();
            $table->dateTime('shipped_date')->nullable();
            $table->string('petugas_vendor')->nullable();
            $table->string('no_surat_jalan_vendor')->nullable();
            $table->string('group_ds_num')->nullable();
            $table->string('ref_ds_num')->nullable();
            $table->integer('ref_ds_line')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->primary(['id', 'ds_num', 'ds_line']);
        });
        Schema::table('delivery', function (Blueprint $table) {
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
        Schema::dropIfExists('delivery');
    }
}
