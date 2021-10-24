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
            $table->id();
            $table->string('ds_num');
            $table->string('ds_line');
            $table->string('ds_type');
            $table->integer('po_line');
            $table->integer('po_release');
            $table->string('description');
            $table->double('order_qty');
            $table->integer('w_serial');
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
            $table->string('group_ds_num');
            $table->string('ref_ds_num');
            $table->integer('ref_ds_line');
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
        Schema::dropIfExists('deliveries');
    }
}
