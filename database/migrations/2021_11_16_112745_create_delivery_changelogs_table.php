<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryChangelogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_changelog', function (Blueprint $table) {
            $table->increments('rowid');
            $table->string('change_type')->length(1);
            $table->dateTime('change_date')->nullable();
            $table->string('change_by')->length(50);
            $table->string('ds_num')->length(15);
            $table->integer('ds_line');
            $table->string('ds_type')->length(2);
            $table->string('po_num')->length(10);
            $table->integer('po_line');
            $table->integer('po_release');
            $table->integer('po_change');
            $table->string('item')->length(30);
            $table->string('description')->length(40);
            $table->double('order_qty')->nullable();
            $table->integer('w_serial')->nullable();
            $table->string('u_m')->length(3);
            $table->dateTime('due_date')->nullable();
            $table->double('unit_price')->nullable();
            $table->string('wh')->length(4);
            $table->string('location')->nullable();
            $table->string('tax_status')->nullable();
            $table->string('currency')->nullable();
            $table->double('old_shipped_qty')->nullable();
            $table->dateTime('old_shipped_date')->nullable();
            $table->string('old_petugas_vendor')->nullable();
            $table->string('old_no_surat_jalan_vendor')->nullable();
            $table->string('new_shipped_qty')->nullable();
            $table->dateTime('new_shipped_date')->nullable();
            $table->string('new_petugas_vendor')->nullable();
            $table->string('new_no_surat_jalan_vendor')->nullable();
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
        Schema::dropIfExists('delivery_changelog');
    }
}
