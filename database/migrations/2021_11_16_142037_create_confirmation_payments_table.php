<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfirmationPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('confirm_payment', function (Blueprint $table) {
            $table->integer('id');
            $table->string('vend_num')->length(10);
            $table->string('ds_num')->length(15);
            $table->integer('ds_line');
            $table->integer('grn_num')->default(0);
            $table->integer('grn_line')->default(0);
            $table->dateTime('due_date')->nullable();
            $table->string('po_num')->nullable();
            $table->integer('po_line')->nullable();
            $table->integer('po_release')->nullable();
            $table->string('item')->nullable();
            $table->string('description')->nullable();
            $table->integer('voucher')->nullable();
            $table->string('no_surat_jalan_vendor')->nullable();
            $table->double('received_qty')->default(0);
            $table->double('rejected_qty')->default(0);
            $table->double('unit_price')->nullable();
            $table->double('harga_sebelum_pajak')->nullable();
            $table->double('ppn')->nullable();
            $table->double('pph')->nullable();
            $table->string('no_faktur_pajak')->length(50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary(['id', 'vend_num', 'ds_num', 'ds_line']);
        });
        Schema::table('confirm_payment', function (Blueprint $table) {
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
        Schema::dropIfExists('confirm_payment');
    }
}
