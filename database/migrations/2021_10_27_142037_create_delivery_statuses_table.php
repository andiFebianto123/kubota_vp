<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('ds_num');
            $table->string('ds_line');
            $table->string('ds_type')->nullable();
            $table->integer('po_line_id');
            $table->integer('po_release');
            $table->string('description');
            $table->integer('grn_num')->default(0);
            $table->integer('grn_line')->default(0);
            $table->integer('received_flag')->default(0);
            $table->dateTime('received_date')->nullable();
            $table->dateTime('payment_plan_date')->nullable();
            $table->integer('payment_in_process_flag')->default(0);
            $table->integer('executed_flag')->default(0);
            $table->dateTime('payment_date')->nullable();
            $table->string('tax_status');
            $table->string('payment_ref_num')->nullable();
            $table->string('bank')->nullable();
            $table->double('shipped_qty')->default(0);
            $table->double('received_qty')->default(0);
            $table->double('rejected_qty')->default(0);
            $table->double('unit_price');
            $table->double('total')->default(0);
            $table->string('petugas_vendor')->nullable();
            $table->string('no_faktur_pajak')->nullable();
            $table->string('no_surat_jalan_vendor')->nullable();
            $table->string('ref_ds_num')->nullable();
            $table->integer('ref_ds_line')->nullable();
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
        Schema::dropIfExists('delivery_statuses');
    }
}
