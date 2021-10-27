<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->integer('po_line');
            $table->integer('po_release');
            $table->string('item');
            $table->string('item_ptki')->nullable();
            $table->string('w_serial')->nullable();
            $table->string('description');
            $table->string('po_change');
            $table->dateTime('po_change_date')->nullable();
            $table->double('order_qty');
            $table->integer('inspection_flag');
            $table->string('u_m');
            $table->dateTime('due_date');
            $table->double('unit_price');
            $table->string('wh');
            $table->string('location');
            $table->string('tax_status');
            $table->string('currency');
            $table->string('item_alias');
            $table->string('status');
            $table->dateTime('production_date')->nullable();
            $table->integer('accept_flag')->default(0);
            $table->string('reason')->nullable();
            $table->string('read_by')->nullable();
            $table->string('read_at')->nullable();
            $table->timestamps();

            $table->foreign('purchase_order_id')
            ->references('id')
            ->on('purchase_orders')
            ->onUpdate('cascade');
        });
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_order_lines');
    }
}
