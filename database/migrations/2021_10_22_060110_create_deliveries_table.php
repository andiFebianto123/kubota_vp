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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ds_num');
            $table->string('ds_line');
            $table->string('ds_type')->nullable();
            $table->integer('po_line_id');
            $table->integer('po_release');
            $table->string('description');
            $table->double('order_qty');
            $table->integer('w_serial')->default(0);
            $table->string('u_m');
            $table->dateTime('due_date');
            $table->double('unit_price');
            $table->string('wh');
            $table->string('location');
            $table->string('tax_status');
            $table->string('currency');
            $table->double('shipped_qty');
            $table->dateTime('shipped_date');
            $table->string('petugas_vendor');
            $table->string('no_surat_jalan_vendor');
            $table->string('group_ds_num')->nullable();
            $table->string('ref_ds_num')->nullable();
            $table->integer('ref_ds_line')->nullable();
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
        Schema::dropIfExists('deliveries');
    }
}
