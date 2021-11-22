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
        Schema::create('delivery_status', function (Blueprint $table) {
            $table->integer('id');
            $table->string('ds_num')->length(10);
            $table->integer('ds_line');
            $table->string('ds_type')->default('00');
            $table->string('po_num')->nullable();
            $table->integer('po_line')->nullable();
            $table->integer('po_release')->nullable();
            $table->string('description')->nullable();
            $table->integer('grn_num')->default(0);
            $table->integer('grn_line')->default(0);
            $table->integer('received_flag')->default(0);
            $table->dateTime('received_date')->nullable();
            $table->dateTime('payment_plan_date')->nullable();
            $table->integer('payment_in_process_flag')->default(0);
            $table->integer('executed_flag')->default(0);
            $table->dateTime('payment_date')->nullable();
            $table->string('tax_status')->nullable();
            $table->string('payment_ref_num')->nullable();
            $table->string('bank')->nullable();
            $table->double('shipped_qty')->default(0);
            $table->double('received_qty')->default(0);
            $table->double('rejected_qty')->default(0);
            $table->double('unit_price')->nullable();
            $table->double('total')->default(0);
            $table->string('petugas_vendor')->nullable();
            $table->string('no_faktur_pajak')->nullable();
            $table->string('no_surat_jalan_vendor')->nullable();
            $table->string('ref_ds_num')->nullable();
            $table->integer('ref_ds_line')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->primary(['id', 'ds_num', 'ds_line']);
        });
        Schema::table('delivery_status', function (Blueprint $table) {
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
        Schema::dropIfExists('delivery_status');
    }
}
